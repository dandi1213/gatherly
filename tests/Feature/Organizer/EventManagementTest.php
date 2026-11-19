<?php

namespace Tests\Feature\Organizer;

use App\Enums\EventStatus;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    private function validEventPayload(array $overrides = []): array
    {
        $start = now()->addWeeks(2);
        $end = (clone $start)->addHours(3);
        $regStart = now();
        $regEnd = (clone $start)->subHour();

        return array_merge([
            'title' => 'Konser Uji Coba',
            'description' => 'Deskripsi event uji coba yang lengkap.',
            'category_id' => Category::factory()->create()->id,
            'format' => 'offline',
            'location' => 'Jakarta',
            'online_url' => null,
            'start_at' => $start->format('Y-m-d H:i:s'),
            'end_at' => $end->format('Y-m-d H:i:s'),
            'registration_start_at' => $regStart->format('Y-m-d H:i:s'),
            'registration_end_at' => $regEnd->format('Y-m-d H:i:s'),
        ], $overrides);
    }

    public function test_organizer_can_create_draft_event(): void
    {
        $organizer = User::factory()->organizer()->create();

        $response = $this->actingAs($organizer)->post(route('organizer.events.store'), $this->validEventPayload());

        $response->assertRedirect(route('organizer.events.index'));
        $this->assertDatabaseHas('events', [
            'organizer_id' => $organizer->id,
            'title' => 'Konser Uji Coba',
            'status' => EventStatus::Draft->value,
        ]);
    }

    public function test_attendee_cannot_access_organizer_area(): void
    {
        $attendee = User::factory()->attendee()->create();

        $this->actingAs($attendee)
            ->get(route('organizer.events.index'))
            ->assertForbidden();
    }

    public function test_organizer_cannot_view_other_organizer_event(): void
    {
        $owner = User::factory()->organizer()->create();
        $other = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $owner->id]);

        $this->actingAs($other)
            ->get(route('organizer.events.show', $event))
            ->assertForbidden();
    }

    public function test_publish_fails_without_ticket_types(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->post(route('organizer.events.publish', $event))
            ->assertSessionHasErrors('publish');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => EventStatus::Draft->value,
        ]);
    }

    public function test_publish_succeeds_with_valid_ticket_type(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        TicketType::factory()->create(['event_id' => $event->id, 'quota' => 50, 'is_active' => true]);

        $this->actingAs($organizer)
            ->post(route('organizer.events.publish', $event))
            ->assertRedirect(route('organizer.events.show', $event));

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => EventStatus::Published->value,
        ]);
    }

    public function test_cancel_event_cancels_all_active_tickets_and_registrations(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->published()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id, 'quota' => 10]);

        $attendee = User::factory()->attendee()->create();

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'ticket_type_id' => $ticketType->id,
            'registration_number' => 'GTH-2601-ABCDEF',
            'attendee_name' => $attendee->name,
            'attendee_email' => $attendee->email,
            'status' => 'registered',
        ]);

        Ticket::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'ticket_code' => str_repeat('a', 40),
            'status' => TicketStatus::Active->value,
        ]);

        $this->actingAs($organizer)
            ->post(route('organizer.events.cancel', $event))
            ->assertRedirect(route('organizer.events.show', $event));

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => EventStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id,
            'status' => TicketStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_ticket_quota_cannot_be_lowered_below_issued(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id, 'quota' => 10]);

        $attendee = User::factory()->attendee()->create();
        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'ticket_type_id' => $ticketType->id,
            'registration_number' => 'GTH-2601-XXXXXX',
            'attendee_name' => $attendee->name,
            'attendee_email' => $attendee->email,
            'status' => 'registered',
        ]);
        Ticket::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'ticket_code' => str_repeat('b', 40),
            'status' => TicketStatus::Active->value,
        ]);

        // issued = 1, coba turunkan kuota jadi 0/1? Aturan: tidak boleh < issued (1). Coba 0 tidak valid (min:1),
        // jadi buat issued = 2 lalu turunkan ke 1.
        $attendee2 = User::factory()->attendee()->create();
        $registration2 = Registration::create([
            'event_id' => $event->id,
            'user_id' => $attendee2->id,
            'ticket_type_id' => $ticketType->id,
            'registration_number' => 'GTH-2601-YYYYYY',
            'attendee_name' => $attendee2->name,
            'attendee_email' => $attendee2->email,
            'status' => 'registered',
        ]);
        Ticket::create([
            'registration_id' => $registration2->id,
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'ticket_code' => str_repeat('c', 40),
            'status' => TicketStatus::Active->value,
        ]);

        $this->actingAs($organizer)
            ->patch(route('organizer.ticket-types.update', [$event, $ticketType]), [
                'name' => $ticketType->name,
                'quota' => 1, // issued = 2 -> harus ditolak
                'is_active' => true,
            ])
            ->assertSessionHasErrors('quota');

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType->id,
            'quota' => 10,
        ]);
    }
}
