<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\EventStaff;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Policies\EventPolicy;
use App\Policies\EventStaffPolicy;
use App\Policies\RegistrationPolicy;
use App\Policies\TicketPolicy;
use App\Policies\TicketTypePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        // Hanya admin yang boleh mengakses panel admin.
        Gate::define('admin.dashboard', fn (User $user) => $user->isAdmin());

        // Register Policies
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(TicketType::class, TicketTypePolicy::class);
        Gate::policy(Registration::class, RegistrationPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(EventStaff::class, EventStaffPolicy::class);
    }
}
