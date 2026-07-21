<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailMessage extends Model
{
    protected $fillable = ['email_conversation_id', 'direction', 'from_name', 'to_name', 'body', 'sent_at'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(EmailConversation::class, 'email_conversation_id');
    }
}
