<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'contact_id',
        'whatsapp_template_id',
        'recipient_name',
        'recipient_number',
        'message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsappTemplate::class, 'whatsapp_template_id');
    }

    /**
     * Universal fallback deep link (wa.me) — works everywhere, opens the
     * WhatsApp Desktop/mobile app if installed, otherwise WhatsApp Web.
     */
    public function waLink(): string
    {
        $number = preg_replace('/[^0-9]/', '', $this->recipient_number);

        return 'https://wa.me/'.$number.'?text='.rawurlencode($this->message);
    }

    /**
     * Faster native-app deep link. Only works if a WhatsApp app is
     * registered as the whatsapp:// handler (e.g. WhatsApp Desktop on
     * Windows) — use waLink() as a fallback when it doesn't open anything.
     */
    public function waAppLink(): string
    {
        $number = preg_replace('/[^0-9]/', '', $this->recipient_number);

        return 'whatsapp://send?phone='.$number.'&text='.rawurlencode($this->message);
    }
}
