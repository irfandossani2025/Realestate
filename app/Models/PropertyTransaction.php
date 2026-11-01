<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['company_id', 'property_id', 'client_id', 'reference', 'type', 'status', 'agreed_value', 'deposit_amount', 'agreement_date', 'handover_date'])]
class PropertyTransaction extends Model
{
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
