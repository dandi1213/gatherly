<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\EventStaffController;
use App\Http\Controllers\Organizer\TicketTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public area
Route::get('/', [PublicEventController::class, 'index'])->name('home');
Route::get('/events', [PublicEventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [PublicEventController::class, 'show'])->name('events.show');

// Health check endpoint for UptimeRobot to keep Render free tier awake
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()], 200);
});

Route::get('/dashboard', function () {
    $user = request()->user();

    return match (true) {
        $user->isOrganizer() => redirect()->route('organizer.events.index'),
        default => Inertia::render('Dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Registrasi event & tiket peserta
    Route::get('/events/{event:slug}/register', [RegistrationController::class, 'create'])->name('events.register');
    Route::post('/events/{event:slug}/register', [RegistrationController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('events.register.store');
    Route::post('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('registrations.cancel');

    Route::get('/my-tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';

// Organizer area
Route::middleware(['auth', 'verified', 'can:create,App\Models\Event'])
    ->prefix('organizer')
    ->name('organizer.')
    ->group(function () {
        Route::get('events', [EventController::class, 'index'])->name('events.index');
        Route::get('events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('events', [EventController::class, 'store'])->name('events.store');
        Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::patch('events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        Route::post('events/{event}/publish', [EventController::class, 'publish'])->name('events.publish');
        Route::post('events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

        // Ticket types (nested)
        Route::post('events/{event}/ticket-types', [TicketTypeController::class, 'store'])->name('ticket-types.store');
        Route::patch('events/{event}/ticket-types/{ticket_type}', [TicketTypeController::class, 'update'])->name('ticket-types.update');
        Route::delete('events/{event}/ticket-types/{ticket_type}', [TicketTypeController::class, 'destroy'])->name('ticket-types.destroy');

        // Staff event (D4)
        Route::get('events/{event}/staff', [EventStaffController::class, 'index'])->name('events.staff.index');
        Route::post('events/{event}/staff', [EventStaffController::class, 'store'])->name('events.staff.store');
        Route::delete('events/{event}/staff/{staff}', [EventStaffController::class, 'destroy'])->name('events.staff.destroy');
    });
require __DIR__.'/auth.php';

// Check-in (organizer pemilik / staff yang ditugaskan)
Route::middleware('auth')->prefix('checkin')->name('checkin.')->group(function () {
    Route::get('/', [CheckInController::class, 'index'])->name('index');
    Route::get('{event}/search', [CheckInController::class, 'search'])->name('search');
    Route::post('{event}/scan', [CheckInController::class, 'scan'])
        ->middleware('throttle:60,1')
        ->name('scan');
    Route::post('{event}/tickets/{ticket}', [CheckInController::class, 'manual'])->name('manual');
    Route::get('{event}', [CheckInController::class, 'show'])->name('show');
});
require __DIR__.'/auth.php';
