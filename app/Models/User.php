<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'birth',
        'phone',
        'address',
        'email',
        'password',
        'avatar',
        'role_id',
        'contract_purpose',
        'contract_form',
        'pay_method',
        'payment_terms',
        'legal_basis',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function contractAttachments(): HasMany
    {
        return $this->hasMany(ContractAttachment::class);
    }

    public function contractNotes(): HasMany
    {
        return $this->hasMany(ContractNote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'recieved_by');
    }

    public function contractAdjustments(): HasMany
    {
        return $this->hasMany(contractAdjustment::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function canAccessFilament(): bool
    {
        return true;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
