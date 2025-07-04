<?php

namespace LaraGram\Laraquest;

use LaraGram\Support\ServiceProvider;

class LaraquestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('laraquest', function () {
            return new Laraquest();
        });
    }
}