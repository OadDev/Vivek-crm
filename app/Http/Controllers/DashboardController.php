<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\EmailConversation;
use App\Models\EmailMessage;
use App\Models\Product;
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

        $weekly = collect(range(6, 0))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);
            $emails = EmailMessage::whereDate('sent_at', $date)->count();
            $whatsapp = WhatsappMessage::whereDate('sent_at', $date)->count();

            return ['label' => $date->format('D'), 'value' => $emails + $whatsapp];
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

        return view('dashboard', compact('stats', 'weekly', 'statusBreakdown', 'statusTotal', 'activities', 'products'));
    }
}
