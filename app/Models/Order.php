<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\DesignType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

class Order extends Model
{
    /** Huruf dan angka tanpa karakter yang mirip (0/O, 1/I). */
    private const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    // Hanya isian pelanggan. user_id, status, total, dp_amount, revision_*, tracking_no,
    // cancel_reason, shipping_cost diisi eksplisit oleh server/admin.
    protected $fillable = [
        'design_type', 'delivery_method', 'courier_id', 'shipping_rate_id',
        'shipping_destination', 'shipping_address', 'payment_method',
    ];

    protected $attributes = [
        'status' => 'baru',
        'payment_status' => 'belum_bayar',
        'shipping_cost' => 0,
        'total' => 0,
        'dp_amount' => 0,
        'revision_count' => 0,
        'revision_fee' => 0,
    ];

    protected function casts(): array
    {
        return [
            'design_type' => DesignType::class,
            'delivery_method' => DeliveryMethod::class,
            'payment_method' => PaymentMethod::class,
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'shipping_cost' => 'integer',
            'total' => 'integer',
            'dp_amount' => 'integer',
            'revision_count' => 'integer',
            'revision_fee' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (blank($order->code)) {
                $order->code = static::generateCode();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Kode pesanan format B: KZK-yymmdd-XXXX (tanggal WIB + 4 karakter acak), unik.
     */
    public static function generateCode(): string
    {
        $date = Date::now(config('app.timezone'))->format('ymd');

        do {
            $suffix = '';
            for ($i = 0; $i < 4; $i++) {
                $suffix .= self::CODE_CHARS[random_int(0, strlen(self::CODE_CHARS) - 1)];
            }
            $code = "KZK-{$date}-{$suffix}";
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    // ---- Relasi ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function shippingRate(): BelongsTo
    {
        return $this->belongsTo(ShippingRate::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ---- Perhitungan (BR-01 sampai BR-03), semua dalam rupiah bulat ----

    /**
     * Harga satuan = harga dasar + tambahan ukuran + jumlah biaya SEMUA area terpilih.
     *
     * @param  iterable<PrintArea>  $areas
     */
    public static function unitPriceFor(Product $product, ?ProductOption $size, iterable $areas): int
    {
        $areaTotal = 0;
        foreach ($areas as $area) {
            $areaTotal += $area->price;
        }

        return $product->base_price + ($size?->extra_price ?? 0) + $areaTotal;
    }

    /** Total = Σ(harga satuan × jumlah) + ongkir (tanpa biaya revisi). */
    public function calculateTotal(): int
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        return $items->sum(fn (OrderItem $item) => $item->unit_price * $item->quantity)
            + $this->shipping_cost;
    }

    /** BR-02: DP = persen DP (pengaturan) × total, dibulatkan ke rupiah terdekat. */
    public static function dpAmountFor(int $total): int
    {
        $percent = (float) Setting::get('dp_percent', 0);

        return (int) round($total * $percent / 100);
    }

    /** Total tagihan = total + biaya revisi (BR-03). */
    protected function grandTotal(): Attribute
    {
        return Attribute::get(fn () => $this->total + $this->revision_fee);
    }

    /** Sisa tagihan = total + biaya revisi - pembayaran terverifikasi. */
    public function remainingBalance(): int
    {
        $paid = $this->payments()
            ->where('status', VerificationStatus::Terverifikasi->value)
            ->sum('amount');

        return max(0, $this->grand_total - (int) $paid);
    }

    // ---- Scope ----

    public function scopeOwnedBy(Builder $query, User|int $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->getKey() : $user);
    }

    /** Memuat semua data untuk halaman detail pesanan admin (bagian 9 handoff). */
    public function scopeWithSpecification(Builder $query): Builder
    {
        return $query->with([
            'user',
            'items.product',
            'designs',
            'courier',
            'shippingRate',
            'payments.verifier',
        ]);
    }

    public function scopeWithStatus(Builder $query, OrderStatus|string|null $status): Builder
    {
        if ($status === null || $status === '') {
            return $query;
        }

        return $query->where('status', $status instanceof OrderStatus ? $status->value : $status);
    }
}
