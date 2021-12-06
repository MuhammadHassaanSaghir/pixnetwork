<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Library\Services\Jwt_Token;

class JWT_Service extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Services\Jwt_Service', function ($app) {
            return new Jwt_Token();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
