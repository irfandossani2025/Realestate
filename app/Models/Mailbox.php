<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'email', 'display_name', 'inbound_host', 'inbound_port', 'outbound_host', 'outbound_port', 'encryption', 'encrypted_credentials', 'last_synced_at', 'is_active'])]
#[Hidden(['encrypted_credentials'])]
class Mailbox extends Model
{
    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('can_send')->withTimestamps();
    }

    /** @return HasMany<MailMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(MailMessage::class);
    }

    protected function casts(): array
    {
        return ['encrypted_credentials' => 'encrypted:array', 'last_synced_at' => 'datetime', 'is_active' => 'boolean'];
    }
}
