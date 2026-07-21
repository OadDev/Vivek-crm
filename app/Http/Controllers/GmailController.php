<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\EmailConversation;
use App\Models\EmailMessage;
use Illuminate\Http\Request;

class GmailController extends Controller
{
    public function index(Request $request)
    {
        $folder = $request->input('folder', 'inbox');
        $filter = $request->input('filter', 'all');

        $query = EmailConversation::query();

        if ($folder === 'starred') {
            $query->where('is_starred', true);
        } else {
            $query->where('folder', $folder);
        }

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        } elseif ($filter === 'starred') {
            $query->where('is_starred', true);
        } elseif ($filter === 'archived') {
            $query->where('folder', 'archive');
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('sender_name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('preview', 'like', "%{$search}%");
            });
        }

        $conversations = $query->orderByDesc('last_message_at')->paginate(12)->withQueryString();

        $folderCounts = [
            'inbox' => EmailConversation::where('folder', 'inbox')->count(),
            'starred' => EmailConversation::where('is_starred', true)->count(),
            'sent' => EmailConversation::where('folder', 'sent')->count(),
            'draft' => EmailConversation::where('folder', 'draft')->count(),
            'archive' => EmailConversation::where('folder', 'archive')->count(),
            'trash' => EmailConversation::where('folder', 'trash')->count(),
        ];

        $selected = null;
        if ($request->filled('conversation')) {
            $selected = EmailConversation::with('messages')->find($request->input('conversation'));

            if ($selected && ! $selected->is_read) {
                $selected->update(['is_read' => true]);
            }
        }

        $selectedContact = $selected?->contact_id ? Contact::find($selected->contact_id) : null;

        return view('gmail.index', compact('conversations', 'folder', 'filter', 'folderCounts', 'selected', 'selectedContact'));
    }

    public function toggleStar(EmailConversation $conversation)
    {
        $conversation->update(['is_starred' => ! $conversation->is_starred]);

        return redirect()->back();
    }

    public function moveFolder(Request $request, EmailConversation $conversation)
    {
        $request->validate(['folder' => ['required', 'in:inbox,sent,draft,archive,trash']]);
        $conversation->update(['folder' => $request->input('folder')]);

        return redirect()->route('gmail.index')->with('success', 'Conversation moved to '.ucfirst($request->input('folder')).'.');
    }

    public function reply(Request $request, EmailConversation $conversation)
    {
        $data = $request->validate(['body' => ['required', 'string']]);

        EmailMessage::create([
            'email_conversation_id' => $conversation->id,
            'direction' => 'outgoing',
            'from_name' => auth()->user()->name,
            'to_name' => $conversation->sender_name,
            'body' => nl2br(e($data['body'])),
            'sent_at' => now(),
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($conversation->contact_id) {
            Contact::where('id', $conversation->contact_id)->update(['last_contacted_at' => now(), 'status' => 'active']);
        }

        Activity::log("Replied to <b>{$conversation->sender_name}</b> — {$conversation->subject}", 'bi-reply-fill', 'primary', $conversation);

        return redirect()->route('gmail.index', ['folder' => $conversation->folder, 'conversation' => $conversation->id])
            ->with('success', 'Reply sent.');
    }

    public function createContact(Request $request, EmailConversation $conversation)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
        ]);

        $contact = Contact::firstOrCreate(
            ['email' => $data['email']],
            $data + ['status' => 'active', 'source' => 'manual', 'last_contacted_at' => now()]
        );

        EmailConversation::where('sender_email', $data['email'])->update(['contact_id' => $contact->id]);

        Activity::log("Contact <b>{$contact->name}</b> created from email conversation", 'bi-person-plus-fill', 'success', $contact);

        return redirect()->route('gmail.index', ['folder' => $conversation->folder, 'conversation' => $conversation->id])
            ->with('success', 'Contact created and linked to this conversation.');
    }
}
