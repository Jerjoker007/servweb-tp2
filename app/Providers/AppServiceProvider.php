<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repository\RepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Repository\AuthRepositoryInterface;
use App\Repository\EquipmentRepositoryInterface;
use App\Repository\ReviewRepositoryInterface;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Eloquent\UserRepository;
use App\Repository\Eloquent\AuthRepository;
use App\Repository\Eloquent\EquipmentRepository;
use App\Repository\Eloquent\ReviewRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(EquipmentRepositoryInterface::class, EquipmentRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
