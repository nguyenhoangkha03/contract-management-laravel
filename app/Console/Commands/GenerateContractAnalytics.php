<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Services\ContractAnalyticsService;

class GenerateContractAnalytics extends Command
{
    protected $signature = 'analytics:generate {--type=monthly : Type of analytics to generate (monthly, quarterly, yearly)}';
    protected $description = 'Generate contract analytics data';

    protected ContractAnalyticsService $analyticsService;

    public function __construct(ContractAnalyticsService $analyticsService)
    {
        parent::__construct();
        $this->analyticsService = $analyticsService;
    }

    public function handle()
    {
        $type = $this->option('type');
        
        $this->info("Generating {$type} contract analytics...");
        
        $contracts = Contract::with(['payments', 'invoices'])->get();
        $progressBar = $this->output->createProgressBar($contracts->count());
        
        foreach ($contracts as $contract) {
            $this->analyticsService->updateContractAnalytics($contract, $type);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->info("\nContract analytics generated successfully!");
        
        // Generate revenue forecasts
        $this->info("Generating revenue forecasts...");
        $forecasts = $this->analyticsService->generateRevenueForecast($type, 12);
        
        foreach ($forecasts as $forecast) {
            $this->analyticsService->saveRevenueForecast($forecast);
        }
        
        $this->info("Revenue forecasts generated successfully!");
        
        return Command::SUCCESS;
    }
}