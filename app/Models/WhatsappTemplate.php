<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappTemplate extends Model
{
    protected $fillable = ['name', 'message'];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    /**
     * Replace {name}, {company}, {employee}, {date} placeholders.
     */
    public function render(array $values): string
    {
        $defaults = [
            'name' => $values['name'] ?? '',
            'company' => $values['company'] ?? config('app.name'),
            'employee' => $values['employee'] ?? auth()->user()?->name ?? '',
            'date' => $values['date'] ?? now()->format('d M Y'),
        ];

        $message = $this->message;
        foreach ($defaults as $key => $value) {
            $message = str_replace('{'.$key.'}', $value, $message);
        }

        return $message;
    }
}
