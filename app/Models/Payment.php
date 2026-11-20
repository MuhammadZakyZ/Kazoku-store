<?php

namespace App\Models;

use App\Enums\PaymentChannel;
use App\Enums\PaymentType;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    // Hanya isian pelanggan. amount, status, reject_reason, verified_by, verified_at
    // diisi eksplisit oleh server/admin.
    protected $fillable = ['type', 'method', 'proof_file'];

    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'method' => PaymentChannel::class,
            'amount' => 'integer',
            'status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->status === VerificationStatus::Terverifikasi;
    }
}
