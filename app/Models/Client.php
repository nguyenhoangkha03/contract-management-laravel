<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Client extends Authenticatable
{
    use HasFactory, Notifiable; // , HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'plain_password', // Thêm field để lưu password gốc tạm thời
        'phone',
        'address',
        'avatar',
        'birth',
        'company_name',
        'tax_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth' => 'date',
    ];

    public function contractParticipants(): HasMany
    {
        return $this->hasMany(ContractParticipant::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    // Auto-generate password khi tạo client mới
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($client) {
            if (empty($client->password)) {
                // Tạo password ngẫu nhiên 8 ký tự
                $plainPassword = self::generatePassword();
                $client->plain_password = $plainPassword; // Lưu password gốc để hiển thị
                $client->password = Hash::make($plainPassword); // Hash để lưu database
            }
        });
    }
    
    // Tạo password ngẫu nhiên
    public static function generatePassword($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }
    
    // Mutator để hash password khi update
    public function setPasswordAttribute($password)
    {
        if ($password && !str_starts_with($password, '$2y$')) {
            $this->attributes['password'] = Hash::make($password);
        } else {
            $this->attributes['password'] = $password;
        }
    }
}
