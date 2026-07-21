<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailConversation extends Model
{
    protected $fillable = [
        'contact_id',
        'sender_name',
        'sender_email',
        'subject',
        'preview',
        'folder',
        'is_read',
        'is_starred',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_starred' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EmailMessage::class)->orderBy('sent_at');
    }
}
