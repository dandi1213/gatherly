<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\EventStaff;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    // ---------- Helpers ----------

    private function openEvent(array $overrides = []): Event
    {
        return Event::factory()->published()->create(array_merge([
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addHour(),
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(4),
        ], $overrides));
    }

    private function ticket(Event $event, array $registrationOverrides = [], array $ticketOverrides = []): Ticket
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'quota' => 50,
        ]);

        $registration = Registration::create(array_merge([
            'event_id' => $event->id,
            'user_id' => User::factory()->attendee()->create()->id,
            'ticket_type_id' => $ticketType->id,
            'registration_number' => 'GTH-2601-'.Str::upper(Str::random(6)),
            'attendee_name' => 'Siti Aminah',
            'attendee_email' => 'siti@example.com',
            'status' => RegistrationStatus::Registered,
        ], $registrationOverrides));

        return Ticket::create(array_merge([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'ticket_code' => bin2hex(random_bytes(32)),
            'status' => TicketStatus::Active,
        ], $ticketOverrides));
    }

    private function assignStaff(Event $event, User $staff, User $organizer): EventStaff
    {
        return EventStaff::create([
            'event_id' => $event->id,
            'user_id' => $staff->id,
            'assigned_by' => $organizer->id,
        ]);
    }

    // ---------- Halaman & otorisasi ----------

    public function test_check_in_index_lists_only_events_user_can_check_in(): void
    {
        $organizer = User::factory()->organizer()->create();
        $ownEvent = $this->openEvent(['organizer_id' => $organizer->id]);

        $staff = User::factory()->attendee()->create();
        $otherOrganizer = User::factory()->organizer()->create();
        $assignedEvent = $this->openEvent(['organizer_id' => $otherOrganizer->id]);
        $this->assignStaff($assignedEvent, $staff, $otherOrganizer);

        // Event lain yang tidak dikelola/ditugaskan tidak boleh muncul.
        $this->openEvent(['organizer_id' => User::factory()->organizer()->create()->id]);

        $this->actingAs($organizer)
            ->get(route('checkin.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('CheckIn/Index')
                ->has('events', 1)
                ->where('events.0.id', $ownEvent->id));

        $this->actingAs($staff)
            ->get(route('checkin.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('CheckIn/Index')
                ->has('events', 1)
                ->where('events.0.id', $assignedEvent->id));

        $this->actingAs(User::factory()->attendee()->create())
            ->get(route('checkin.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('CheckIn/Index')
                ->has('events', 0));
    }

    public function test_organizer_can_open_check_in_page_with_stats_and_window(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $this->ticket($event);

        $this->actingAs($organizer)
            ->get(route('checkin.show', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('CheckIn/Show')
                ->where('event.id', $event->id)
                ->where('stats.checked_in', 0)
                ->where('stats.total', 1)
                ->where('window.open', true));
    }

    public function test_assigned_staff_can_open_check_in_page(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $staff = User::factory()->attendee()->create();
        $this->assignStaff($event, $staff, $organizer);

        $this->actingAs($staff)
            ->get(route('checkin.show', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('CheckIn/Show'));
    }

    public function test_unassigned_attendee_cannot_open_check_in_page(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);

        $this->actingAs(User::factory()->attendee()->create())
            ->get(route('checkin.show', $event))
            ->assertForbidden();
    }

    public function test_organizer_of_another_event_cannot_scan(): void
    {
        $owner = User::factory()->organizer()->create();
        $other = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $owner->id]);
        $ticket = $this->ticket($event);

        $this->actingAs($other)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code])
            ->assertForbidden();

        $this->assertSame(TicketStatus::Active, $ticket->fresh()->status);
    }

    // ---------- Scan QR ----------

    public function test_organizer_can_check_in_valid_ticket_via_scan(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $ticket = $this->ticket($event);

        $response = $this->actingAs($organizer)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code]);

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'reason' => 'ok',
                'stats' => ['checked_in' => 1, 'total' => 1],
            ])
            ->assertJsonPath('ticket.attendee_name', 'Siti Aminah')
            ->assertJsonPath('ticket.status', TicketStatus::CheckedIn->value);

        $fresh = $ticket->fresh();

        $this->assertSame(TicketStatus::CheckedIn, $fresh->status);
        $this->assertSame($organizer->id, $fresh->checked_in_by);
        $this->assertNotNull($fresh->checked_in_at);
    }

    public function test_assigned_staff_can_scan_and_is_recorded_as_officer(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $staff = User::factory()->attendee()->create();
        $this->assignStaff($event, $staff, $organizer);
        $ticket = $this->ticket($event);

        $this->actingAs($staff)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame($staff->id, $ticket->fresh()->checked_in_by);
    }

    public function test_second_scan_is_rejected_without_changing_check_in_time(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $ticket = $this->ticket($event);

        $this->actingAs($organizer)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $firstCheckedInAt = $ticket->fresh()->checked_in_at;

        $this->actingAs($organizer)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code])
            ->assertOk()
            ->assertJson([
                'ok' => false,
                'reason' => 'already_checked_in',
            ])
            ->assertJsonPath('ticket.checked_in_by', $organizer->name);

        $this->assertTrue($firstCheckedInAt->equalTo($ticket->fresh()->checked_in_at));
        $this->assertSame(
            1,
            Ticket::where('event_id', $event->id)
                ->where('status', TicketStatus::CheckedIn->value)
                ->count(),
        );
    }

    public function test_ticket_from_another_event_is_rejected(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $otherEvent = $this->openEvent(['organizer_id' => $organizer->id]);
        $ticket = $this->ticket($otherEvent);

        $this->actingAs($organizer)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code])
            ->assertOk()
            ->assertJson([
                'ok' => false,
                'reason' => 'wrong_event',
                'ticket' => null,
            ]);

        $this->assertSame(TicketStatus::Active, $ticket->fresh()->status);
    }

    public function test_unknown_token_is_rejected_without_leaking_ticket_data(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->postJson(route('checkin.scan', $event), ['code' => str_repeat('a', 64)])
            ->assertOk()
            ->assertJson([
                'ok' => false,
                'reason' => 'not_found',
                'ticket' => null,
            ]);
    }

    public function test_cancelled_ticket_is_rejected(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $ticket = $this->ticket($event, [], ['status' => TicketStatus::Cancelled]);

        $this->actingAs($organizer)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code])
            ->assertOk()
            ->assertJson([
                'ok' => false,
                'reason' => 'cancelled',
            ]);
    }

    public function test_check_in_outside_window_is_rejected(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent([
            'organizer_id' => $organizer->id,
            'start_at' => now()->addHours(5),
            'end_at' => now()->addHours(8),
        ]);
        $ticket = $this->ticket($event);

        $this->actingAs($organizer)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code])
            ->assertOk()
            ->assertJson([
                'ok' => false,
                'reason' => 'outside_window',
            ]);

        $this->assertSame(TicketStatus::Active, $ticket->fresh()->status);
    }

    public function test_draft_event_cannot_be_checked_in(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(4),
        ]);
        $ticket = $this->ticket($event);

        $this->actingAs($organizer)
            ->postJson(route('checkin.scan', $event), ['code' => $ticket->ticket_code])
            ->assertOk()
            ->assertJson([
                'ok' => false,
                'reason' => 'event_inactive',
            ]);
    }

    // ---------- Manual check-in ----------

    public function test_manual_check_in_uses_the_same_rules_as_scan(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $ticket = $this->ticket($event);

        $this->actingAs($organizer)
            ->post(route('checkin.manual', [$event, $ticket]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(TicketStatus::CheckedIn, $ticket->fresh()->status);

        $this->actingAs($organizer)
            ->post(route('checkin.manual', [$event, $ticket]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(
            1,
            Ticket::where('event_id', $event->id)
                ->where('status', TicketStatus::CheckedIn->value)
                ->count(),
        );
    }

    public function test_manual_check_in_rejects_ticket_from_another_event(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $otherEvent = $this->openEvent(['organizer_id' => $organizer->id]);
        $ticket = $this->ticket($otherEvent);

        $this->actingAs($organizer)
            ->post(route('checkin.manual', [$event, $ticket]))
            ->assertNotFound();

        $this->assertSame(TicketStatus::Active, $ticket->fresh()->status);
    }

    // ---------- Pencarian manual ----------

    public function test_search_finds_participant_by_name_email_and_registration_number(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $this->ticket($event, [
            'attendee_name' => 'Siti Aminah',
            'attendee_email' => 'siti.aminah@example.com',
            'registration_number' => 'GTH-2601-SITI01',
        ]);

        foreach (['Siti', 'siti.aminah@example.com', 'GTH-2601-SITI01'] as $query) {
            $this->actingAs($organizer)
                ->get(route('checkin.search', ['event' => $event, 'q' => $query]))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('CheckIn/Show')
                    ->where('search', $query)
                    ->has('results.data', 1)
                    ->where('results.data.0.attendee_name', 'Siti Aminah')
                    ->where('results.data.0.status', TicketStatus::Active->value));
        }
    }

    public function test_search_does_not_return_cancelled_registrations(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->openEvent(['organizer_id' => $organizer->id]);
        $this->ticket($event, ['status' => RegistrationStatus::Cancelled]);

        $this->actingAs($organizer)
            ->get(route('checkin.search', ['event' => $event, 'q' => 'Siti']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('results.data', 0));
    }
}
