<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Reminder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.app', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $dueReminders = Reminder::with('contact')
                ->where('user_id', Auth::id())
                ->where('is_done', false)
                ->where('remind_at', '<=', now())
                ->orderBy('remind_at')
                ->limit(10)
                ->get();

            $globalRecentActivities = Activity::latest()->limit(8)->get();

            $view->with(compact('dueReminders', 'globalRecentActivities'));
        });
    }
}
