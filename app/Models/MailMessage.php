<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mailbox_id', 'contact_id', 'lead_id', 'property_id', 'external_id', 'folder', 'from_address', 'from_name', 'to_addresses', 'subject', 'preview', 'body_html', 'is_read', 'is_flagged', 'received_at'])]
class MailMessage extends Model
{
    /** @return BelongsTo<Mailbox, $this> */
    public function mailbox(): BelongsTo
    {
        return $this->belongsTo(Mailbox::class);
    }

    protected function casts(): array
    {
        return ['to_addresses' => 'array', 'is_read' => 'boolean', 'is_flagged' => 'boolean', 'received_at' => 'datetime'];
    }
}
