<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'property_transaction_id', 'reference', 'amount', 'method', 'status', 'received_on', 'proof_path', 'notes'])]
class Payment extends Model
{
    /** @return BelongsTo<PropertyTransaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PropertyTransaction::class, 'property_transaction_id');
    }
}
