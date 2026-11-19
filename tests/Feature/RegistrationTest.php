<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Mail\TicketIssuedMail;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    // ---------- Helpers ----------

    private function openEvent(array $overrides = []): Event
    {
        return Event::factory()->published()->create(array_merge([
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(3),
        ], $overrides));
    }

    private function ticketType(Event $event, array $overrides = []): TicketType
    {
        return TicketType::factory()->create(array_merge([
            'event_id' => $event->id,
            'quota' => 10,
            'is_active' => true,
        ], $overrides));
    }

    private function payload(TicketType $ticketType, array $overrides = []): array
    {
        return array_merge([
            'ticket_type_id' => $ticketType->id,
            'attendee_name' => 'Budi Peserta',
            'attendee_email' => 'budi@example.com',
            'attendee_phone' => '08123456789',
        ], $overrides);
    }

    private function makeRegistration(User $user, Event $event, TicketType $ticketType, string $number = 'GTH-2601-AAAAAA'): Registration
    {
        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
            'registration_number' => $number,
            'attendee_name' => $user->name,
            'attendee_email' => $user->email,
            'status' => RegistrationStatus::Registered,
        ]);

        Ticket::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'ticket_code' => bin2hex(random_bytes(32)),
            'status' => TicketStatus::Active,
        ]);

        return $registration->load('ticket');
    }

    // ---------- Public access ----------

    public function test_guest_is_redirected_to_login_when_opening_registration_form(): void
    {
        $event = $this->openEvent();
        $this->ticketType($event);

        $this->get(route('events.register', $event->slug))
            ->assertRedirect(route('login'));
    }

    public function test_guest_returns_to_registration_page_after_login(): void
    {
        $event = $this->openEvent();
        $this->ticketType($event);
        $user = User::factory()->attendee()->create([
            'email' => 'kembali@example.com',
            'password' => 'password',
        ]);

        $this->get(route('events.register', $event->slug))
            ->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => 'kembali@example.com',
            'password' => 'password',
        ])->assertRedirect(route('events.register', $event->slug));

        $this->assertAuthenticatedAs($user);
    }

    public function test_published_event_detail_is_visible_to_public(): void
    {
        $event = $this->openEvent();
        $this->ticketType($event);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Events/Show')
                ->where('event.slug', $event->slug)
                ->has('ticketTypes', 1)
                ->where('registrationOpen', true));
    }

    public function test_draft_event_detail_is_hidden_from_public(): void
    {
        $event = Event::factory()->create([
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
        ]);

        $this->get(route('events.show', $event->slug))->assertNotFound();
    }

    public function test_organizer_can_preview_own_draft_event(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
        ]);

        $this->actingAs($organizer)
            ->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Events/Show')
                ->where('isPreview', true));
    }

    // ---------- Registration success ----------

    public function test_attendee_can_register_and_receives_ticket_and_email(): void
    {
        $user = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event);

        $response = $this->actingAs($user)->post(
            route('events.register.store', $event->slug),
            $this->payload($ticketType),
        );

        $registration = Registration::firstOrFail();

        $response->assertRedirect(route('tickets.show', $registration->ticket));
        $this->assertSame(RegistrationStatus::Registered, $registration->status);
        $this->assertSame($user->id, $registration->user_id);
        $this->assertSame('Budi Peserta', $registration->attendee_name);
        $this->assertMatchesRegularExpression('/^GTH-\d{4}-[A-F0-9]{6}$/', $registration->registration_number);

        $ticket = $registration->ticket;
        $this->assertNotNull($ticket);
        $this->assertSame(TicketStatus::Active, $ticket->status);
        $this->assertSame(64, strlen($ticket->ticket_code));

        Mail::assertQueued(TicketIssuedMail::class, function (TicketIssuedMail $mail) use ($registration) {
            return $mail->registration->id === $registration->id;
        });
    }

    // ---------- Rejections ----------

    public function test_draft_event_rejects_registration(): void
    {
        $user = User::factory()->attendee()->create();
        $event = Event::factory()->create([
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(3),
        ]);
        $ticketType = $this->ticketType($event);

        $this->actingAs($user)
            ->post(route('events.register.store', $event->slug), $this->payload($ticketType))
            ->assertSessionHasErrors('event');

        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseCount('tickets', 0);
        Mail::assertNothingQueued();
    }

    public function test_cancelled_event_rejects_registration(): void
    {
        $user = User::factory()->attendee()->create();
        $event = Event::factory()->cancelled()->create([
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(3),
        ]);
        $ticketType = $this->ticketType($event);

        $this->actingAs($user)
            ->post(route('events.register.store', $event->slug), $this->payload($ticketType))
            ->assertSessionHasErrors('event');

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_registration_outside_period_is_rejected(): void
    {
        $user = User::factory()->attendee()->create();
        $event = $this->openEvent([
            'registration_start_at' => now()->subDays(10),
            'registration_end_at' => now()->subDay(),
        ]);
        $ticketType = $this->ticketType($event);

        $this->actingAs($user)
            ->post(route('events.register.store', $event->slug), $this->payload($ticketType))
            ->assertSessionHasErrors('event');

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_registration_is_rejected_when_quota_is_exhausted(): void
    {
        $user = User::factory()->attendee()->create();
        $other = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event, ['quota' => 1]);

        $this->makeRegistration($other, $event, $ticketType, 'GTH-2601-HABIS1');

        $this->actingAs($user)
            ->post(route('events.register.store', $event->slug), $this->payload($ticketType))
            ->assertSessionHasErrors('ticket_type_id');

        $this->assertSame(1, Registration::count());
        $this->assertSame(1, Ticket::count());
    }

    public function test_duplicate_active_registration_is_rejected(): void
    {
        $user = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event);

        $this->makeRegistration($user, $event, $ticketType, 'GTH-2601-DUPLIK');

        $this->actingAs($user)
            ->post(route('events.register.store', $event->slug), $this->payload($ticketType))
            ->assertSessionHasErrors('event');

        $this->assertSame(1, Registration::count());
    }

    public function test_ticket_type_from_another_event_is_rejected(): void
    {
        $user = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $otherEvent = $this->openEvent();
        $foreignTicketType = $this->ticketType($otherEvent);

        $this->actingAs($user)
            ->post(route('events.register.store', $event->slug), $this->payload($foreignTicketType))
            ->assertSessionHasErrors('ticket_type_id');

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_inactive_ticket_type_is_rejected(): void
    {
        $user = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event, ['is_active' => false]);

        $this->actingAs($user)
            ->post(route('events.register.store', $event->slug), $this->payload($ticketType))
            ->assertSessionHasErrors('ticket_type_id');

        $this->assertDatabaseCount('registrations', 0);
    }

    // ---------- My tickets & authorization ----------

    public function test_my_tickets_page_only_lists_own_registrations(): void
    {
        $owner = User::factory()->attendee()->create();
        $other = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event);

        $this->makeRegistration($owner, $event, $ticketType, 'GTH-2601-OWNER1');

        $this->actingAs($owner)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Index')
                ->has('registrations.data', 1)
                ->where('registrations.data.0.registration_number', 'GTH-2601-OWNER1'));

        $this->actingAs($other)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('registrations.data', 0));
    }

    public function test_attendee_cannot_view_another_users_ticket(): void
    {
        $owner = User::factory()->attendee()->create();
        $other = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event);

        $registration = $this->makeRegistration($owner, $event, $ticketType, 'GTH-2601-PRIVAT');

        $this->actingAs($other)
            ->get(route('tickets.show', $registration->ticket))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('tickets.show', $registration->ticket))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Show')
                ->where('ticket.registration.registration_number', 'GTH-2601-PRIVAT')
                ->has('qrSvg'));
    }

    // ---------- Cancellation ----------

    public function test_attendee_can_cancel_registration_and_quota_returns(): void
    {
        $user = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event, ['quota' => 1]);

        $registration = $this->makeRegistration($user, $event, $ticketType, 'GTH-2601-CANCEL');

        $this->actingAs($user)
            ->post(route('registrations.cancel', $registration))
            ->assertRedirect(route('tickets.index'));

        $this->assertSame(RegistrationStatus::Cancelled, $registration->fresh()->status);
        $this->assertSame(TicketStatus::Cancelled, $registration->ticket->fresh()->status);
        $this->assertSame(1, $ticketType->fresh()->remaining_quota);

        // Setelah batal, user boleh mendaftar lagi selama kuota tersedia.
        $this->actingAs($user)
            ->post(route('events.register.store', $event->slug), $this->payload($ticketType))
            ->assertRedirect();

        $this->assertSame(2, Registration::count());
        $this->assertSame(1, Registration::where('status', RegistrationStatus::Registered->value)->count());
    }

    public function test_other_user_cannot_cancel_registration(): void
    {
        $owner = User::factory()->attendee()->create();
        $other = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event);
        $registration = $this->makeRegistration($owner, $event, $ticketType, 'GTH-2601-NOCANC');

        $this->actingAs($other)
            ->post(route('registrations.cancel', $registration))
            ->assertForbidden();

        $this->assertSame(RegistrationStatus::Registered, $registration->fresh()->status);
    }

    // ---------- QR ----------

    public function test_ticket_mail_renders_with_inline_qr(): void
    {
        $user = User::factory()->attendee()->create();
        $event = $this->openEvent();
        $ticketType = $this->ticketType($event);
        $registration = $this->makeRegistration($user, $event, $ticketType, 'GTH-2601-MAILQR');

        $html = (new TicketIssuedMail($registration))->render();

        $this->assertStringContainsString('cid:ticket-qr.png@gatherly', $html);
        $this->assertStringContainsString($registration->registration_number, $html);
    }

    public function test_qr_png_and_svg_can_be_generated(): void
    {
        $service = app(QrCodeService::class);

        $png = $service->png('test-token-'.str_repeat('a', 32));
        $this->assertStringStartsWith("\x89PNG", $png);

        $svg = $service->svg('test-token-'.str_repeat('a', 32));
        $this->assertStringContainsString('<svg', $svg);
    }
}
