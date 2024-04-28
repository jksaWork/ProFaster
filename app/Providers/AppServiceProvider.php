<?php

namespace App\Providers;

use App\Models\OrganizationProfile;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema as FacadesSchema;
// use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            $Profile = OrganizationProfile::first();
            View::share('OrganizationProfile', $Profile);
        } catch (Exception $e) {
        }
        Schema::defaultStringLength(100);
        Paginator::useBootstrap();
    }
}
