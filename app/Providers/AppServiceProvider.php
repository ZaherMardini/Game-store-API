<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Model::unguard();
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    Gate::define('Super user', fn(User $user) => $user->sudo);
  }
}
