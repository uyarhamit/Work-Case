<?php

use App\Mail\EventReminder;
use App\Models\Events;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Mail;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(function () {
            $today = now()->format('Y-m-d');
            
            Events::where('is_expired', '1')->whereDate('start_date', '<', $today)->update(['is_expired' => '0']);

            $today_events = Events::where('is_expired', '1')->whereDate('start_date', $today)->get();
            foreach ($today_events as $key => $today_event) {
                foreach ($today_event->eventAttendees as $sub_key => $eventAttendee) {
                    Mail::to($eventAttendee->user->email)->send(new EventReminder($today_event, $eventAttendee->user));
                }
            }

        })->daily();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
