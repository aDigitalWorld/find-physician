<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\ZohoSync',
        'App\Console\Commands\ZohoRemoveTags',
        'App\Console\Commands\ZohoSyncDevices'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('zoho:sync')->dailyAt('01:00')->timezone('America/New_York')->emailOutputTo(env('DEBUG_EMAIL'));
        $schedule->command('zoho:syncDevices')->dailyAt('12:00')->timezone('America/New_York')->emailOutputTo(env('DEBUG_EMAIL'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

