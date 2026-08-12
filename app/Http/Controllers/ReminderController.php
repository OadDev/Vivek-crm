<?php

namespace App\Http\Controllers;

use App\Models\Reminder;

class ReminderController extends Controller
{
    public function done(Reminder $reminder)
    {
        abort_unless($reminder->user_id === auth()->id(), 403);

        $reminder->update(['is_done' => true]);

        return redirect()->back()->with('success', 'Reminder dismissed.');
    }
}
