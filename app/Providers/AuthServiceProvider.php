<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\House;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Sejour;
use App\Policies\ProfilePolicy;
use App\Policies\ReservationPolicy;
use App\Policies\RoomPolicy;
use App\Policies\SejourPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

//        Gate::policy(Room::class, RoomPolicy::class);
//        Gate::policy(Sejour::class, SejourPolicy::class);
//        Gate::policy(Profile::class, ProfilePolicy::class);
//        Gate::policy(Reservation::class, ReservationPolicy::class);


    }
}
