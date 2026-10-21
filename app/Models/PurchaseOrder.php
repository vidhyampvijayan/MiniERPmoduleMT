<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Database\Factories\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    /** @use HasFactory<PurchaseOrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number',
        'supplier_id',
        'status',
        'total_amount',
        'notes',
        'approved_at',
        'received_at',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isImmutable()
    {
        return $this->status->isImmutable();
    }

    public function canTransitionTo($target)
    {
        return $this->status->canTransitionTo($target);
    }
}
