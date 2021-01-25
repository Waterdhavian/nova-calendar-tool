<?php

namespace Waterdhavian\NovaCalendarTool;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Waterdhavian\NovaCalendarTool\Console\Commands\ExportEvents;
use Waterdhavian\NovaCalendarTool\Console\Commands\ImportEvents;
use Waterdhavian\NovaCalendarTool\Http\Middleware\Authorize;
use Waterdhavian\NovaCalendarTool\Models\Event;
use Waterdhavian\NovaCalendarTool\Observers\EventObserver;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-calendar-tool');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_events_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_events_table.php'),
        ], 'migrations');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            if ( ! is_null(config('google-calendar.calendar_id')))
            {
                Event::observe(EventObserver::class);
            }
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/nova-calendar-tool')
                ->namespace('Waterdhavian\NovaCalendarTool\Http\Controllers')
                ->group(__DIR__.'/../routes/api.php');

        $this->commands([
            ImportEvents::class,
            ExportEvents::class
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
