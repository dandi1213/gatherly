<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicEventTest extends TestCase
{
    use RefreshDatabase;

    private function publishedEvent(array $overrides = []): Event
    {
        return Event::factory()->published()->create(array_merge([
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(3),
        ], $overrides));
    }

    public function test_homepage_lists_only_published_events(): void
    {
        $published = $this->publishedEvent(['title' => 'Event Publik']);
        $draft = Event::factory()->create(['title' => 'Event Draft']);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Events/Index')
                ->has('events.data', 1)
                ->where('events.data.0.title', 'Event Publik'));
    }

    public function test_events_index_supports_search_and_category_filter(): void
    {
        $category = Category::factory()->create(['name' => 'Teknologi']);

        $this->publishedEvent(['title' => 'Laravel Meetup Bandung']);
        $this->publishedEvent(['title' => 'Festival Musik Jakarta']);
        $this->publishedEvent([
            'title' => 'Workshop Desain',
            'category_id' => $category->id,
        ]);

        $this->get(route('events.index', ['q' => 'laravel']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('events.data', 1)
                ->where('events.data.0.title', 'Laravel Meetup Bandung'));

        $this->get(route('events.index', ['category' => $category->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('events.data', 1)
                ->where('events.data.0.title', 'Workshop Desain'));
    }

    public function test_past_events_are_hidden_from_upcoming_list_and_shown_in_past_filter(): void
    {
        $this->publishedEvent(['title' => 'Event Akan Datang']);

        Event::factory()->published()->create([
            'title' => 'Event Lampau',
            'start_at' => now()->subWeeks(2),
            'end_at' => now()->subWeeks(2)->addHours(3),
            'registration_start_at' => now()->subMonth(),
            'registration_end_at' => now()->subWeeks(2),
        ]);

        $this->get(route('events.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('events.data', 1)
                ->where('events.data.0.title', 'Event Akan Datang'));

        $this->get(route('events.index', ['period' => 'past']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('events.data', 1)
                ->where('events.data.0.title', 'Event Lampau'));
    }

    public function test_event_detail_exposes_ticket_types_and_remaining_quota(): void
    {
        $event = $this->publishedEvent();
        TicketType::factory()->create(['event_id' => $event->id, 'quota' => 5, 'name' => 'VIP']);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Events/Show')
                ->has('ticketTypes', 1)
                ->where('ticketTypes.0.name', 'VIP')
                ->where('ticketTypes.0.remaining_quota', 5)
                ->where('ticketTypes.0.is_available_now', true));
    }

    public function test_disabled_event_is_hidden_from_public(): void
    {
        $event = $this->publishedEvent(['is_disabled' => true]);

        $this->get(route('events.show', $event->slug))->assertNotFound();
        $this->get(route('events.index'))
            ->assertInertia(fn (Assert $page) => $page->has('events.data', 0));
    }

    public function test_cancelled_event_detail_is_hidden_from_public(): void
    {
        $event = Event::factory()->cancelled()->create();

        $this->get(route('events.show', $event->slug))->assertNotFound();
    }

    public function test_registration_form_is_prefilled_from_profile(): void
    {
        $user = User::factory()->attendee()->create([
            'name' => 'Profil Peserta',
            'email' => 'profil@example.com',
        ]);
        $event = $this->publishedEvent();
        TicketType::factory()->create(['event_id' => $event->id, 'quota' => 5]);

        $this->actingAs($user)
            ->get(route('events.register', $event->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Events/Register')
                ->where('defaults.attendee_name', 'Profil Peserta')
                ->where('defaults.attendee_email', 'profil@example.com')
                ->has('ticketTypes', 1));
    }

    public function test_already_registered_user_is_redirected_to_ticket_from_form(): void
    {
        $user = User::factory()->attendee()->create();
        $event = $this->publishedEvent();
        $ticketType = TicketType::factory()->create(['event_id' => $event->id, 'quota' => 5]);

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
            'registration_number' => 'GTH-2601-SUDAH1',
            'attendee_name' => $user->name,
            'attendee_email' => $user->email,
            'status' => 'registered',
        ]);
        $ticket = Ticket::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'ticket_code' => str_repeat('d', 64),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('events.register', $event->slug))
            ->assertRedirect(route('tickets.show', $ticket));
    }
}
