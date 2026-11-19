<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventStaffTest extends TestCase
{
    use RefreshDatabase;

    private function event(User $organizer, array $overrides = []): Event
    {
        return Event::factory()->published()->create(array_merge([
            'organizer_id' => $organizer->id,
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(3),
        ], $overrides));
    }

    public function test_organizer_can_view_staff_page(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);
        $staff = User::factory()->attendee()->create();

        EventStaff::create([
            'event_id' => $event->id,
            'user_id' => $staff->id,
            'assigned_by' => $organizer->id,
        ]);

        $this->actingAs($organizer)
            ->get(route('organizer.events.staff.index', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Organizer/Events/Staff')
                ->where('event.id', $event->id)
                ->has('staff', 1)
                ->where('staff.0.email', $staff->email));
    }

    public function test_organizer_can_add_registered_user_as_staff_by_email(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);
        $staff = User::factory()->attendee()->create(['email' => 'staff@example.com']);

        $this->actingAs($organizer)
            ->post(route('organizer.events.staff.store', $event), [
                'email' => 'STAFF@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('event_staff', [
            'event_id' => $event->id,
            'user_id' => $staff->id,
            'assigned_by' => $organizer->id,
        ]);
    }

    public function test_unregistered_email_is_rejected(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);

        $this->actingAs($organizer)
            ->post(route('organizer.events.staff.store', $event), [
                'email' => 'belum-terdaftar@example.com',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('event_staff', 0);
    }

    public function test_duplicate_staff_assignment_is_rejected(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);
        $staff = User::factory()->attendee()->create();

        EventStaff::create([
            'event_id' => $event->id,
            'user_id' => $staff->id,
            'assigned_by' => $organizer->id,
        ]);

        $this->actingAs($organizer)
            ->post(route('organizer.events.staff.store', $event), [
                'email' => $staff->email,
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('event_staff', 1);
    }

    public function test_organizer_cannot_add_themselves_as_staff(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);

        $this->actingAs($organizer)
            ->post(route('organizer.events.staff.store', $event), [
                'email' => $organizer->email,
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('event_staff', 0);
    }

    public function test_inactive_user_cannot_be_added_as_staff(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);
        $staff = User::factory()->attendee()->create(['is_active' => false]);

        $this->actingAs($organizer)
            ->post(route('organizer.events.staff.store', $event), [
                'email' => $staff->email,
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('event_staff', 0);
    }

    public function test_other_organizer_cannot_manage_staff(): void
    {
        $owner = User::factory()->organizer()->create();
        $other = User::factory()->organizer()->create();
        $event = $this->event($owner);
        $staff = User::factory()->attendee()->create();

        $this->actingAs($other)
            ->get(route('organizer.events.staff.index', $event))
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('organizer.events.staff.store', $event), [
                'email' => $staff->email,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('event_staff', 0);
    }

    public function test_staff_member_cannot_manage_staff_list(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);
        $staff = User::factory()->attendee()->create();

        EventStaff::create([
            'event_id' => $event->id,
            'user_id' => $staff->id,
            'assigned_by' => $organizer->id,
        ]);

        $this->actingAs($staff)
            ->get(route('organizer.events.staff.index', $event))
            ->assertForbidden();
    }

    public function test_organizer_can_remove_staff(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);
        $staff = User::factory()->attendee()->create();

        $assignment = EventStaff::create([
            'event_id' => $event->id,
            'user_id' => $staff->id,
            'assigned_by' => $organizer->id,
        ]);

        $this->actingAs($organizer)
            ->delete(route('organizer.events.staff.destroy', [$event, $assignment]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('event_staff', ['id' => $assignment->id]);
    }

    public function test_removing_staff_from_another_event_returns_not_found(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = $this->event($organizer);
        $otherEvent = $this->event($organizer);
        $staff = User::factory()->attendee()->create();

        $assignment = EventStaff::create([
            'event_id' => $otherEvent->id,
            'user_id' => $staff->id,
            'assigned_by' => $organizer->id,
        ]);

        $this->actingAs($organizer)
            ->delete(route('organizer.events.staff.destroy', [$event, $assignment]))
            ->assertNotFound();

        $this->assertDatabaseHas('event_staff', ['id' => $assignment->id]);
    }
}
