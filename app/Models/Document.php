<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['company_id', 'uploaded_by', 'category', 'language', 'original_name', 'path', 'mime_type', 'size', 'checksum', 'is_public'])]
class Document extends Model
{
    /** @return MorphTo<Model, $this> */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }
}
