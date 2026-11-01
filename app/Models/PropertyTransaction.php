<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'property_id', 'client_id', 'reference', 'type', 'status', 'agreed_value', 'deposit_amount', 'agreement_date', 'handover_date'])]
class PropertyTransaction extends Model
{
    /** @return BelongsTo<Property, $this> */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /** @return BelongsTo<Contact, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'client_id');
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
