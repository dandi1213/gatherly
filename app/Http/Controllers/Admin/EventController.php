<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class EventController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Event::query()
                ->with(['organizer', 'category']))
                ->filterColumn('title', function ($model) use ($request) {
                    $model->where('title', 'like', '%'.$request->get('search')['value'].'%');
                })
                ->filterColumn('organizer_name', function ($model) use ($request) {
                    $model->whereHas('organizer', function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->get('search')['value'].'%');
                    });
                })
                ->filterColumn('category_name', function ($model) use ($request) {
                    $model->whereHas('category', function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->get('search')['value'].'%');
                    });
                })
                ->editColumn('organizer_name', fn ($model) => $model->organizer?->name ?? '—')
                ->editColumn('category_name', fn ($model) => $model->category?->name ?? '—')
                ->editColumn('status_badge', fn ($model) => '<span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold '.match ($model->status->value) {
                    'draft' => 'border-warning-100 bg-warning-50 text-warning-700',
                    'published' => 'border-success-100 bg-success-50 text-success-700',
                    'cancelled' => 'border-danger-100 bg-danger-50 text-danger-700',
                }.'">'.e($model->status->label()).'</span>')
                ->editColumn('actions', fn ($model) => view('admin.events.partials._actions', ['event' => $model]))
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        $events = Event::with(['organizer', 'category'])
            ->latest()
            ->paginate(10);

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'format' => 'required|in:online,offline,hybrid',
            'location' => 'required_if:format,offline',
            'online_url' => 'required_if:format,online',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'registration_start_at' => 'required|date',
            'registration_end_at' => 'required|date|after:registration_start_at',
        ]);

        $data['organizer_id'] = auth()->user()->id;
        $data['status'] = 'draft';
        $data['slug'] = strtolower(str_replace([' ', '/', '\\'], '-', $data['title'])).'-'.rand(1000, 9999);

        Event::create($data);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil dibuat sebagai draft.');
    }

    public function edit(Event $event)
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'format' => 'sometimes|required|in:online,offline,hybrid',
            'location' => 'required_if:format,offline',
            'online_url' => 'required_if:format,online',
            'start_at' => 'sometimes|required|date',
            'end_at' => 'sometimes|required|date|after:start_at',
            'registration_start_at' => 'sometimes|required|date',
            'registration_end_at' => 'sometimes|required|date|after:registration_start_at',
        ]);

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $event->$key = $data[$key];
            }
        }

        $event->save();

        return back()->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        if ($event->registrations()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus event yang memiliki registrasi.');
        }

        $event->delete();

        return back()->with('success', 'Event berhasil dihapus.');
    }
}
