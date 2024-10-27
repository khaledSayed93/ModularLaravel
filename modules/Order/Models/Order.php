<?php

namespace Modules\Order\Models;

use App\Models\User;
use Modules\Payment\Payment;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Dto\CartItemCollection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Order\Exceptions\OrderMissingOrderLinesException;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_in_cents',
        'payment_gateway',
        'payment_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_in_cents' => 'integer',
    ];

    public function url()
    {
        return route('order::orders.show', $this->id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function lastPayment(): HasOne
    {
        return $this->payments()->one()->latest();
    }

    public static function startForUser($userId): self
    {
        return self::make([
            'status' => 'completed',
            'user_id' => $userId,
        ]);
    }

    /**
     * @param CartItemCollection $cartItems
     * @return void
     */
    public function addLinesFromCartItems($cartItems): void
    {
        foreach ($cartItems->items() as $cartItem) {
            $this->lines->push(OrderLine::make([
                'product_id' => $cartItem->product->id,
                'product_price_in_cents' => $cartItem->product->priceInCents,
                'quantity' => $cartItem->quantity,
            ]));
        }

        $this->total_in_cents = $this->lines->sum(fn(OrderLine $line) => $line->product_price_in_cents);
    }

    /**
     * Summary of fulfill
     * @throws OrderMissingOrderLinesException
     * @return void
     */

    public function fulfill()
    {
        throw_if($this->lines->isEmpty(), new OrderMissingOrderLinesException());

        $this->status = 'completed';

        $this->save();
        $this->lines()->saveMany($this->lines);
    }
}
