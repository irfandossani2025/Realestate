<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'property_transaction_id', 'reference', 'starts_on', 'ends_on', 'rent_amount', 'rent_frequency', 'security_deposit', 'next_payment_due_on', 'renewal_notice_days', 'renewal_status', 'status'])]
class Lease extends Model
{
    /** @return BelongsTo<PropertyTransaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PropertyTransaction::class, 'property_transaction_id');
    }

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'next_payment_due_on' => 'date'];
    }
}
