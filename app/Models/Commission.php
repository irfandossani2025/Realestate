<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'property_transaction_id', 'user_id', 'calculation_type', 'rate', 'basis_amount', 'amount', 'status', 'paid_on'])]
class Commission extends Model
{
    /** @return BelongsTo<PropertyTransaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PropertyTransaction::class, 'property_transaction_id');
    }

    /** @return BelongsTo<User, $this> */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
