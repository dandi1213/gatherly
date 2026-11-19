<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\TicketType;
use App\Models\User;
use App\Services\RegistrationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Validation\ValidationException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
        ]);

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@gatherly.test'],
            [
                'name' => 'Admin Gatherly',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        // Organizer
        User::firstOrCreate(
            ['email' => 'organizer@gatherly.test'],
            [
                'name' => 'Organizer Demo',
                'role' => 'organizer',
                'org_name' => 'Gatherly Events Corp',
                'org_description' => 'Penyelenggara event profesional di platform Gatherly.',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        // Attendee
        User::firstOrCreate(
            ['email' => 'attendee@gatherly.test'],
            [
                'name' => 'Attendee Demo',
                'role' => 'attendee',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        // Staff demo (akun attendee yang ditugaskan ke event tertentu)
        User::firstOrCreate(
            ['email' => 'staff@gatherly.test'],
            [
                'name' => 'Staff Demo',
                'role' => 'attendee',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        $this->seedDemoEvents();
    }

    /**
     * Event demo yang sudah Published agar halaman publik & alur registrasi
     * bisa langsung dicoba setelah migrate:fresh --seed.
     */
    private function seedDemoEvents(): void
    {
        $organizer = User::where('email', 'organizer@gatherly.test')->firstOrFail();
        $technology = Category::where('slug', 'teknologi')->first();
        $music = Category::where('slug', 'musik')->first();

        $start = now()->addWeeks(3)->setTime(19, 0);

        $meetup = Event::firstOrCreate(
            ['slug' => 'gatherly-tech-meetup-2026'],
            [
                'organizer_id' => $organizer->id,
                'category_id' => $technology?->id,
                'title' => 'Gatherly Tech Meetup 2026',
                'description' => "Meetup komunitas developer untuk berbagi pengalaman membangun produk digital.\n\nAgenda: sesi talks, demo produk, dan networking. Cocok untuk pemula maupun profesional.",
                'format' => 'offline',
                'location' => 'Jakarta Convention Center, Jakarta',
                'online_url' => null,
                'start_at' => $start,
                'end_at' => $start->copy()->addHours(4),
                'registration_start_at' => now()->subWeek(),
                'registration_end_at' => $start->copy()->subHour(),
                'status' => EventStatus::Published,
                'is_disabled' => false,
                'published_at' => now(),
            ]
        );

        TicketType::firstOrCreate(
            ['event_id' => $meetup->id, 'name' => 'Reguler'],
            [
                'description' => 'Akses seluruh sesi talks dan networking.',
                'quota' => 100,
                'is_active' => true,
            ]
        );

        TicketType::firstOrCreate(
            ['event_id' => $meetup->id, 'name' => 'VIP'],
            [
                'description' => 'Akses talks, area networking khusus, dan merchandise.',
                'quota' => 20,
                'is_active' => true,
            ]
        );

        $onlineStart = now()->addWeeks(5)->setTime(13, 0);

        $workshop = Event::firstOrCreate(
            ['slug' => 'gatherly-online-workshop'],
            [
                'organizer_id' => $organizer->id,
                'category_id' => $music?->id,
                'title' => 'Workshop Online: Membangun Brand Event',
                'description' => 'Workshop online interaktif membahas strategi membangun brand event, konten promosi, dan pengalaman peserta.',
                'format' => 'online',
                'location' => null,
                'online_url' => 'https://meet.example.com/gatherly-workshop',
                'start_at' => $onlineStart,
                'end_at' => $onlineStart->copy()->addHours(2),
                'registration_start_at' => now()->subWeek(),
                'registration_end_at' => $onlineStart->copy()->subHour(),
                'status' => EventStatus::Published,
                'is_disabled' => false,
                'published_at' => now(),
            ]
        );

        TicketType::firstOrCreate(
            ['event_id' => $workshop->id, 'name' => 'Gratis'],
            [
                'description' => 'Tiket gratis, kuota terbatas.',
                'quota' => 200,
                'is_active' => true,
            ]
        );

        $this->seedCheckInDemo($organizer);
    }

    /**
     * Event yang sedang berlangsung agar alur check-in bisa langsung dicoba:
     * staff demo ditugaskan dan attendee demo sudah memiliki tiket.
     */
    private function seedCheckInDemo(User $organizer): void
    {
        $staff = User::where('email', 'staff@gatherly.test')->first();
        $attendee = User::where('email', 'attendee@gatherly.test')->first();

        $start = now()->addHour();
        $category = Category::where('slug', 'komunitas')->first();

        $event = Event::updateOrCreate(
            ['slug' => 'gatherly-community-day'],
            [
                'organizer_id' => $organizer->id,
                'category_id' => $category?->id,
                'title' => 'Gatherly Community Day',
                'description' => "Hari berkumpul komunitas Gatherly: sharing session, workshop singkat, dan networking.\n\nEvent demo ini berlangsung hari ini agar alur check-in QR dapat langsung dicoba.",
                'format' => 'offline',
                'location' => 'Coworking Space, Jakarta',
                'online_url' => null,
                'start_at' => $start,
                'end_at' => $start->copy()->addHours(4),
                'registration_start_at' => now()->subDay(),
                'registration_end_at' => $start->copy()->addHours(4),
                'status' => EventStatus::Published,
                'is_disabled' => false,
                'published_at' => now(),
            ]
        );

        $ticketType = TicketType::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Umum'],
            [
                'description' => 'Tiket gratis untuk seluruh sesi Community Day.',
                'quota' => 100,
                'is_active' => true,
            ]
        );

        if ($staff && ! $event->staff()->whereKey($staff->id)->exists()) {
            $event->staff()->attach($staff->id, [
                'assigned_by' => $organizer->id,
            ]);
        }

        if ($attendee && ! Registration::where('event_id', $event->id)
            ->where('user_id', $attendee->id)
            ->where('status', RegistrationStatus::Registered->value)
            ->exists()) {
            try {
                app(RegistrationService::class)->register($attendee, $event, $ticketType, [
                    'attendee_name' => $attendee->name,
                    'attendee_email' => $attendee->email,
                ]);
            } catch (ValidationException) {
                // Seeder tetap boleh selesai meski event demo sudah tidak bisa didaftari.
            }
        }
    }
}
