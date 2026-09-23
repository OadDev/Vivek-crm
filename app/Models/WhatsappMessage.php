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

    /**
     * Same link-building logic as waAppLink()/waLink(), without needing a
     * saved (or even persistable) record -- used to render a contact's
     * WhatsApp button with real links already in the HTML, so the click
     * handler can navigate the instant it's clicked instead of waiting on
     * a network round-trip first (which breaks the whatsapp:// handoff on
     * mobile browsers -- see app-js.blade.php).
     *
     * @return array{0: string, 1: string} [$appLink, $webLink]
     */
    public static function previewLinks(string $number, string $message): array
    {
        $preview = new self(['recipient_number' => $number, 'message' => $message]);

        return [$preview->waAppLink(), $preview->waLink()];
    }
}
