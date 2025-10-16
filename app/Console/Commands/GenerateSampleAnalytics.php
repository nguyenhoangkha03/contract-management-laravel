<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\ContractAnalytics;
use Carbon\Carbon;

class GenerateSampleAnalytics extends Command
{
    protected $signature = 'analytics:sample';
    protected $description = 'Generate sample analytics data for testing';

    public function handle()
    {
        $this->info('Generating sample analytics data...');
        
        // Get all contracts or create sample ones
        $contracts = Contract::all();
        
        if ($contracts->isEmpty()) {
            $this->warn('No contracts found. Creating sample contracts...');
            $this->createSampleContracts();
            $contracts = Contract::all();
        }
        
        $progressBar = $this->output->createProgressBar($contracts->count() * 12);
        
        foreach ($contracts as $contract) {
            // Generate analytics for the last 12 months
            for ($i = 0; $i < 12; $i++) {
                $periodDate = Carbon::now()->subMonths($i)->startOfMonth();
                
                ContractAnalytics::updateOrCreate(
                    [
                        'contract_id' => $contract->id,
                        'period_date' => $periodDate,
                        'period_type' => 'monthly',
                    ],
                    [
                        'total_value' => $contract->total_value ?? rand(10000000, 50000000),
                        'paid_amount' => rand(1000000, ($contract->total_value ?? 50000000) * 0.8),
                        'outstanding_amount' => function() use ($contract) {
                            $total = $contract->total_value ?? rand(10000000, 50000000);
                            $paid = rand(1000000, $total * 0.8);
                            return $total - $paid;
                        },
                        'payment_percentage' => rand(20, 95),
                        'contract_duration_days' => rand(30, 365),
                        'days_since_start' => rand(0, 200),
                        'days_to_end' => rand(0, 100),
                        'completion_percentage' => rand(10, 120),
                        'performance_status' => $this->getRandomPerformanceStatus(),
                        'is_overdue' => rand(0, 1) > 0.8,
                        'overdue_days' => rand(0, 30),
                        'monthly_revenue' => rand(1000000, 10000000),
                        'projected_revenue' => rand(5000000, 40000000),
                        'risk_score' => rand(0, 100),
                        'invoice_count' => rand(1, 5),
                        'payment_count' => rand(1, 10),
                        'average_payment_days' => rand(5, 45),
                    ]
                );
                
                $progressBar->advance();
            }
        }
        
        $progressBar->finish();
        $this->info("\nSample analytics data generated successfully!");
        
        return Command::SUCCESS;
    }
    
    private function createSampleContracts()
    {
        $sampleContracts = [
            [
                'contract_number' => 'HD001',
                'total_value' => 50000000,
                'start_date' => Carbon::now()->subMonths(6),
                'end_date' => Carbon::now()->addMonths(6),
                'description' => 'Sample Contract 1',
            ],
            [
                'contract_number' => 'HD002',
                'total_value' => 30000000,
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->addMonths(9),
                'description' => 'Sample Contract 2',
            ],
            [
                'contract_number' => 'HD003',
                'total_value' => 75000000,
                'start_date' => Carbon::now()->subMonths(12),
                'end_date' => Carbon::now()->addMonths(3),
                'description' => 'Sample Contract 3',
            ],
        ];
        
        foreach ($sampleContracts as $contractData) {
            Contract::create($contractData);
        }
    }
    
    private function getRandomPerformanceStatus(): string
    {
        $statuses = ['excellent', 'good', 'normal', 'poor', 'critical'];
        $weights = [0.15, 0.25, 0.35, 0.20, 0.05]; // Probability distribution
        
        $random = rand(1, 100) / 100;
        $cumulative = 0;
        
        foreach ($statuses as $index => $status) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $status;
            }
        }
        
        return 'normal';
    }
}