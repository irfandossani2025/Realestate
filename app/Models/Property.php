<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['company_id', 'owner_id', 'reference', 'property_type', 'purpose', 'status', 'publication_status', 'title', 'title_ar', 'description', 'description_ar', 'city', 'area', 'address', 'latitude', 'longitude', 'bedrooms', 'bathrooms', 'area_sqm', 'sale_price', 'rent_price', 'rent_period', 'published_at'])]
class Property extends Model
{
    use HasFactory;

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Contact, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'owner_id');
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** @return MorphMany<Document, $this> */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
