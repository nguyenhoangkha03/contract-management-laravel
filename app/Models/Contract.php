<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Client;
use App\Models\ContractType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
        'client_id',
        'contract_type_id',
        'start_date',
        'end_date',
        'total_value',
        'description',
        'contract_status_id',
        'salesperson_id',
        'manager_id',
        'accountant_id',
        'implementer_id',
        'contract_purpose',
        'contract_form',
        'pay_method',
        'payment_terms',
        'legal_basis',
        'payment_requirements'
    ];

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function contractStatus(): BelongsTo
    {
        return $this->belongsTo(ContractStatus::class);
    }

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'sales_employee_id');
    // }

    public function salesEmployee()
    {
        return $this->belongsTo(User::class, 'sales_employee_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function contractAttachments(): HasMany
    {
        return $this->hasMany(ContractAttachment::class);
    }

    public function contractNotes(): HasMany
    {
        return $this->hasMany(ContractNote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function contractAdjustments(): HasMany
    {
        return $this->hasMany(contractAdjustment::class);
    }

    public function contractProducts(): HasMany
    {
        return $this->hasMany(ContractProduct::class);
    }

    public function contractParticipants(): HasMany
    {
        return $this->hasMany(ContractParticipant::class);
    }

    public function contractTerms(): HasMany
    {
        return $this->hasMany(ContractTerm::class);
    }
}
