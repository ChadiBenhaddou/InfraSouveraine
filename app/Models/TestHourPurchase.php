<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestHourPurchase extends Model
{
    protected $fillable = [
        'tenant_id',
        'hours_purchased',
        'amount_paid_cents',
        'paypal_order_id',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
