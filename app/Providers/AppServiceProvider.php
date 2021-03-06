<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind(
            'Api\Repositories\Interfaces\UserRepositoryInterface',
            'Api\Repositories\UserRepository'
        );

        $this->app->bind(
            'Api\Repositories\Interfaces\CategoryRepositoryInterface',
            'Api\Repositories\CategoryRepository'
        );

    }
}
