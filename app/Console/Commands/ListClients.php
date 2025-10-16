<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Contract;

class ListClients extends Command
{
    protected $signature = 'client:list';
    protected $description = 'List all clients and their login status';

    public function handle()
    {
        $clients = Client::with('contracts')->get();

        if ($clients->isEmpty()) {
            $this->info("Không có clients nào trong database.");
            return Command::SUCCESS;
        }

        $this->info("📋 Danh sách Clients:");
        $this->newLine();

        $headers = ['Name', 'Email', 'Phone', 'Has Password', 'Contracts', 'Can Login'];
        $rows = [];

        foreach ($clients as $client) {
            $rows[] = [
                $client->name ?: 'N/A',
                $client->email ?: 'N/A',
                $client->phone ?: 'N/A',
                $client->password ? '✅ Yes' : '❌ No',
                $client->contracts->count(),
                $client->password ? '✅ Yes' : '❌ No',
            ];
        }

        $this->table($headers, $rows);

        $withPassword = $clients->where('password', '!=', null)->count();
        $withoutPassword = $clients->where('password', null)->count();

        $this->newLine();
        $this->info("📊 Tổng kết:");
        $this->line("- Clients có thể đăng nhập: {$withPassword}");
        $this->line("- Clients cần tạo password: {$withoutPassword}");

        if ($withoutPassword > 0) {
            $this->newLine();
            $this->info("💡 Để tạo tài khoản đăng nhập:");
            $this->line("php artisan client:create-account <email> <password>");
        }

        return Command::SUCCESS;
    }
}