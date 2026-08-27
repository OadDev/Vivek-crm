<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\EmailConversation;
use App\Models\EmailMessage;
use App\Models\Product;
use App\Models\Reminder;
use App\Models\WhatsappMessage;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalContacts' => Contact::count(),
            'unreadEmails' => EmailConversation::where('folder', 'inbox')->where('is_read', false)->count(),
            'totalConversations' => EmailConversation::count(),
            'repliedToday' => EmailMessage::where('direction', 'outgoing')->whereDate('sent_at', today())->count(),
            'whatsappToday' => WhatsappMessage::whereDate('sent_at', today())->count(),
            'totalProducts' => Product::count(),
        ];

        // One grouped-by-day query per model instead of 2 queries x 7 days.
        $rangeStart = today()->subDays(6)->startOfDay();
        $rangeEnd = today()->endOfDay();

        $emailsByDay = EmailMessage::whereBetween('sent_at', [$rangeStart, $rangeEnd])
            ->selectRaw('DATE(sent_at) as d, count(*) as c')->groupBy('d')->pluck('c', 'd');

        $whatsappByDay = WhatsappMessage::whereBetween('sent_at', [$rangeStart, $rangeEnd])
            ->selectRaw('DATE(sent_at) as d, count(*) as c')->groupBy('d')->pluck('c', 'd');

        $weekly = collect(range(6, 0))->map(function ($daysAgo) use ($emailsByDay, $whatsappByDay) {
            $date = today()->subDays($daysAgo);
            $key = $date->format('Y-m-d');
            $value = ($emailsByDay[$key] ?? 0) + ($whatsappByDay[$key] ?? 0);

            return ['label' => $date->format('D'), 'value' => $value];
        });

        $maxWeekly = max(1, $weekly->max('value'));
        $weekly = $weekly->map(fn ($d) => $d + ['percent' => max(6, round($d['value'] / $maxWeekly * 100))]);

        $statusBreakdown = [
            'replied' => EmailConversation::where('folder', 'sent')->count(),
            'open' => EmailConversation::where('folder', 'inbox')->where('is_read', false)->count(),
            'pending' => EmailConversation::where('folder', 'inbox')->where('is_read', true)->count(),
            'overdue' => EmailConversation::where('folder', 'archive')->count(),
        ];
        $statusTotal = max(1, array_sum($statusBreakdown));

        $activities = Activity::latest()->limit(6)->get();
        $products = Product::latest()->limit(5)->get();

        // Every pending reminder, soonest first — not just ones due within
        // the next day, so a freshly-set 7-day reminder is still visible
        // here right away instead of the block looking empty/hidden.
        $followUpsDue = Reminder::with('contact')
            ->where('user_id', auth()->id())
            ->where('is_done', false)
            ->orderBy('remind_at')
            ->limit(8)
            ->get();

        return view('dashboard', compact('stats', 'weekly', 'statusBreakdown', 'statusTotal', 'activities', 'products', 'followUpsDue'));
    }
}
