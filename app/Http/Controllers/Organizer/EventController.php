<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Category;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(private readonly EventService $eventService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $events = Event::query()
            ->where('organizer_id', $user->id)
            ->with('category')
            ->withCount('registrations')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Organizer/Events/Index', [
            'events' => $events,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Organizer/Events/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['organizer_id'] = $request->user()->id;
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['status'] = EventStatus::Draft;
        $data['poster_path'] = $this->storePoster($request);

        unset($data['poster']);

        Event::create($data);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event berhasil disimpan sebagai draft.');
    }

    public function show(Request $request, Event $event): Response
    {
        $this->authorizeEvent($request, $event);

        $event->load('category', 'ticketTypes');

        return Inertia::render('Organizer/Events/Show', [
            'event' => $event,
            'publishErrors' => $this->eventService->publishValidationErrors($event),
        ]);
    }

    public function edit(Request $request, Event $event): Response
    {
        $this->authorizeEvent($request, $event);

        $event->load('ticketTypes');

        return Inertia::render('Organizer/Events/Edit', [
            'event' => $event,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($request, $event);

        if ($event->isCancelled()) {
            return back()->with('error', 'Event yang sudah dibatalkan bersifat read-only.');
        }

        $data = $request->validated();

        if ($request->hasFile('poster')) {
            // Hapus poster lama
            if ($event->poster_path) {
                $this->deletePoster($event->poster_path);
            }
            $data['poster_path'] = $this->storePoster($request);
        }

        unset($data['poster']);

        $event->update($data);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($request, $event);

        if (! $event->isDraft()) {
            return back()->with('error', 'Hanya event berstatus Draft yang dapat dihapus.');
        }

        if ($event->poster_path) {
            $this->deletePoster($event->poster_path);
        }

        $event->delete();

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    public function publish(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($request, $event);

        $this->eventService->publish($event);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Event berhasil dipublikasikan.');
    }

    public function cancel(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($request, $event);

        $this->eventService->cancel($event);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Event telah dibatalkan dan seluruh tiket aktif dibatalkan.');
    }

    // ---------- Helpers ----------

    private function authorizeEvent(Request $request, Event $event): void
    {
        abort_unless($event->isManagedBy($request->user()), 403);
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (Event::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function storePoster(Request $request): ?string
    {
        if (! $request->hasFile('poster')) {
            return null;
        }

        $disk = $this->posterDisk();

        return $request->file('poster')->store('events', $disk);
    }

    private function deletePoster(string $path): void
    {
        Storage::disk($this->posterDisk())->delete($path);
    }

    /**
     * Gunakan Supabase bila terkonfigurasi, jika tidak fallback ke disk publik lokal.
     */
    private function posterDisk(): string
    {
        return config('filesystems.disks.supabase.key') ? 'supabase' : 'public';
    }
}
