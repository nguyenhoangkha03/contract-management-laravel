<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Generate daily contract analytics
        $schedule->command('analytics:generate --type=monthly')
            ->dailyAt('02:00')
            ->description('Generate monthly contract analytics');

        // Generate quarterly analytics on the first day of each quarter
        $schedule->command('analytics:generate --type=quarterly')
            ->quarterly()
            ->description('Generate quarterly contract analytics');

        // Generate yearly analytics on January 1st
        $schedule->command('analytics:generate --type=yearly')
            ->yearly()
            ->description('Generate yearly contract analytics');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}