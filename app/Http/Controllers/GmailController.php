<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\EmailConversation;
use App\Models\EmailMessage;
use App\Models\GmailAccount;
use App\Services\GmailApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class GmailController extends Controller
{
    public function index(Request $request)
    {
        $folder = $request->input('folder', 'inbox');
        $filter = $request->input('filter', 'all');

        $myAccountId = GmailAccount::forUser(auth()->user())->id;

        $query = EmailConversation::query()
            ->where(function ($q) use ($myAccountId) {
                $q->where('gmail_account_id', $myAccountId)->orWhereNull('gmail_account_id');
            });

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
                    ->orWhere('sender_email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('preview', 'like', "%{$search}%");
            });
        }

        $conversations = $query->orderByDesc('last_message_at')->paginate(12)->withQueryString();

        $mine = fn () => EmailConversation::query()->where(function ($q) use ($myAccountId) {
            $q->where('gmail_account_id', $myAccountId)->orWhereNull('gmail_account_id');
        });

        // One grouped count query instead of one COUNT(*) per folder.
        $byFolder = $mine()->selectRaw('folder, count(*) as cnt')->groupBy('folder')->pluck('cnt', 'folder');

        $folderCounts = [
            'inbox' => $byFolder['inbox'] ?? 0,
            'starred' => $mine()->where('is_starred', true)->count(),
            'sent' => $byFolder['sent'] ?? 0,
            'draft' => $byFolder['draft'] ?? 0,
            'archive' => $byFolder['archive'] ?? 0,
            'trash' => $byFolder['trash'] ?? 0,
        ];

        $selected = null;
        if ($request->filled('conversation')) {
            // Scoped to this user's own account (or unowned legacy rows),
            // same as the main listing query above -- otherwise anyone
            // could read any other user's conversation by just passing a
            // different ?conversation= id.
            $selected = EmailConversation::with('messages')
                ->where(function ($q) use ($myAccountId) {
                    $q->where('gmail_account_id', $myAccountId)->orWhereNull('gmail_account_id');
                })
                ->find($request->input('conversation'));

            if ($selected && ! $selected->is_read) {
                $selected->update(['is_read' => true]);
            }
        }

        $selectedContact = $selected?->contact_id ? Contact::find($selected->contact_id) : null;

        return view('gmail.index', compact('conversations', 'folder', 'filter', 'folderCounts', 'selected', 'selectedContact'));
    }

    public function toggleStar(EmailConversation $conversation)
    {
        $this->authorizeConversation($conversation);

        $conversation->update(['is_starred' => ! $conversation->is_starred]);

        return redirect()->back();
    }

    public function moveFolder(Request $request, EmailConversation $conversation)
    {
        $this->authorizeConversation($conversation);

        $request->validate(['folder' => ['required', 'in:inbox,sent,draft,archive,trash']]);
        $conversation->update(['folder' => $request->input('folder')]);

        return redirect()->route('gmail.index')->with('success', 'Conversation moved to '.ucfirst($request->input('folder')).'.');
    }

    public function reply(Request $request, EmailConversation $conversation, GmailApiService $gmail)
    {
        $this->authorizeConversation($conversation);

        $data = $request->validate(['body' => ['required', 'string']]);
        $bodyHtml = nl2br(e($data['body']));

        if (auth()->user()->html_signature) {
            $bodyHtml .= '<br><br>'.auth()->user()->html_signature;
        }

        $account = GmailAccount::forUser(auth()->user());

        $sentViaGmail = false;
        $sendError = null;

        if ($account->isConnected()) {
            try {
                $gmail->sendReply($conversation, $bodyHtml, $account);
                $sentViaGmail = true;
            } catch (Throwable $e) {
                $sendError = $e->getMessage();
                Log::warning('Gmail send failed: '.$e->getMessage());
            }
        }

        EmailMessage::create([
            'email_conversation_id' => $conversation->id,
            'direction' => 'outgoing',
            'from_name' => auth()->user()->name,
            'to_name' => $conversation->sender_name,
            'body' => $bodyHtml,
            'sent_at' => now(),
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($conversation->contact_id) {
            Contact::where('id', $conversation->contact_id)->update(['last_contacted_at' => now(), 'status' => 'active']);
        }

        Activity::log("Replied to <b>".e($conversation->sender_name)."</b> — ".e($conversation->subject), 'bi-reply-fill', 'primary', $conversation);

        $status = $sentViaGmail
            ? 'success'
            : ($account->isConnected() ? 'error' : 'success');

        $message = $sentViaGmail
            ? 'Reply sent via Gmail.'
            : ($account->isConnected()
                ? 'Saved locally, but sending via Gmail failed: '.$sendError
                : 'Reply saved locally (connect Gmail in Settings to send for real).');

        return redirect()->route('gmail.index', ['folder' => $conversation->folder, 'conversation' => $conversation->id])
            ->with($status, $message);
    }

    public function createContact(Request $request, EmailConversation $conversation)
    {
        $this->authorizeConversation($conversation);

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

        Activity::log("Contact <b>".e($contact->name)."</b> created from email conversation", 'bi-person-plus-fill', 'success', $contact);

        return redirect()->route('gmail.index', ['folder' => $conversation->folder, 'conversation' => $conversation->id])
            ->with('success', 'Contact created and linked to this conversation.');
    }

    /**
     * Any logged-in user could otherwise read, star, move, reply through
     * (sending real outbound email from someone else's connected Gmail
     * account), or link a contact to any conversation just by guessing an
     * id -- these routes had no ownership check at all. A conversation with
     * no gmail_account_id (pre-dates per-user Gmail) is treated as shared.
     */
    protected function authorizeConversation(EmailConversation $conversation): void
    {
        if ($conversation->gmail_account_id === null) {
            return;
        }

        $myAccountId = GmailAccount::forUser(auth()->user())->id;

        abort_unless($conversation->gmail_account_id === $myAccountId, 403);
    }
}
