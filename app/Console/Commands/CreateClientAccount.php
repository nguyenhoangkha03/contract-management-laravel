<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Contract;

class CreateClientAccount extends Command
{
    protected $signature = 'client:create-account {email} {password}';
    protected $description = 'Create login account for existing client';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Tìm client theo email
        $client = Client::where('email', $email)->first();

        if (!$client) {
            $this->error("Client với email {$email} không tồn tại!");
            
            // Hiển thị danh sách clients hiện có
            $clients = Client::select('name', 'email')->get();
            if ($clients->isNotEmpty()) {
                $this->info("Clients hiện có:");
                foreach ($clients as $c) {
                    $this->line("- {$c->name} ({$c->email})");
                }
            } else {
                $this->info("Không có clients nào trong database.");
            }
            
            return Command::FAILURE;
        }

        // Cập nhật password cho client
        $client->update([
            'password' => $password, // Sẽ được hash tự động
        ]);

        $this->info("✅ Đã tạo tài khoản đăng nhập cho {$client->name}!");
        $this->info("📧 Email: {$email}");
        $this->info("🔑 Password: {$password}");

        // Hiển thị thông tin hợp đồng nếu có
        $contracts = Contract::where('client_id', $client->id)->count();
        if ($contracts > 0) {
            $this->info("📋 Client này có {$contracts} hợp đồng");
        }

        return Command::SUCCESS;
    }
}