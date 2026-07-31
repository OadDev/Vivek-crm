<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\EmailConversation;
use App\Models\EmailMessage;
use App\Models\GmailAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GmailApiService
{
    protected const API_BASE = 'https://gmail.googleapis.com/gmail/v1/users/me';

    protected const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    /**
     * Returns a valid access token for the connected account, refreshing it
     * first if it's expired (or about to be).
     */
    public function accessToken(GmailAccount $account): string
    {
        if (! $account->isConnected()) {
            throw new RuntimeException('No Gmail account is connected.');
        }

        if ($account->access_token && $account->token_expires_at && $account->token_expires_at->isAfter(now()->addMinute())) {
            return $account->access_token;
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $account->resolvedClientId(),
            'client_secret' => $account->resolvedClientSecret(),
            'refresh_token' => $account->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Could not refresh the Gmail access token: '.$response->body());
        }

        $data = $response->json();

        $account->update([
            'access_token' => $data['access_token'],
            'token_expires_at' => now()->addSeconds(($data['expires_in'] ?? 3600) - 60),
        ]);

        return $data['access_token'];
    }

    /**
     * Pulls the most recent inbox messages from Gmail and upserts them into
     * email_conversations / email_messages. Safe to call repeatedly --
     * existing messages (matched by gmail_message_id) are left untouched.
     */
    public function syncInbox(int $maxResults = 25): array
    {
        $account = GmailAccount::current();
        $token = $this->accessToken($account);

        $list = Http::withToken($token)
            ->get(self::API_BASE.'/messages', [
                'maxResults' => $maxResults,
                'labelIds' => 'INBOX',
            ]);

        if (! $list->successful()) {
            throw new RuntimeException('Could not list Gmail messages (HTTP '.$list->status().'): '.$list->body());
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($list->json('messages', []) as $ref) {
            if (EmailMessage::where('gmail_message_id', $ref['id'])->exists()) {
                $skipped++;

                continue;
            }

            $full = Http::withToken($token)->get(self::API_BASE.'/messages/'.$ref['id'], ['format' => 'full']);

            if (! $full->successful()) {
                $skipped++;

                continue;
            }

            $this->storeMessage($full->json());
            $created++;
        }

        $account->update([
            'last_synced_at' => now(),
            'last_sync_status' => 'success',
            'last_sync_message' => "Fetched {$created} new message(s), skipped {$skipped} already-synced.",
        ]);

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }

    protected function storeMessage(array $message): void
    {
        $headers = collect($message['payload']['headers'] ?? [])
            ->mapWithKeys(fn ($h) => [strtolower($h['name']) => $h['value']]);

        $fromHeader = $headers->get('from', '');
        [$senderName, $senderEmail] = $this->parseFromHeader($fromHeader);

        $labels = $message['labelIds'] ?? [];
        $folder = match (true) {
            in_array('DRAFT', $labels, true) => 'draft',
            in_array('SENT', $labels, true) => 'sent',
            in_array('TRASH', $labels, true) => 'trash',
            ! in_array('INBOX', $labels, true) => 'archive',
            default => 'inbox',
        };

        $contact = Contact::where('email', $senderEmail)->first();

        $conversation = EmailConversation::updateOrCreate(
            ['gmail_thread_id' => $message['threadId']],
            [
                'contact_id' => $contact?->id,
                'sender_name' => $senderName ?: $senderEmail,
                'sender_email' => $senderEmail,
                'subject' => $headers->get('subject', '(no subject)'),
                'preview' => $message['snippet'] ?? '',
                'folder' => $folder,
                'is_read' => ! in_array('UNREAD', $labels, true),
                'is_starred' => in_array('STARRED', $labels, true),
                'last_message_at' => isset($message['internalDate'])
                    ? now()->createFromTimestampMs((int) $message['internalDate'])
                    : now(),
            ]
        );

        EmailMessage::updateOrCreate(
            ['gmail_message_id' => $message['id']],
            [
                'email_conversation_id' => $conversation->id,
                'message_id_header' => $headers->get('message-id'),
                'direction' => in_array('SENT', $labels, true) ? 'outgoing' : 'incoming',
                'from_name' => $senderName ?: $senderEmail,
                'to_name' => $headers->get('to', ''),
                'body' => $this->extractBody($message['payload'] ?? []),
                'sent_at' => isset($message['internalDate'])
                    ? now()->createFromTimestampMs((int) $message['internalDate'])
                    : now(),
            ]
        );
    }

    protected function parseFromHeader(string $from): array
    {
        if (preg_match('/^(.*?)\s*<(.+?)>$/', trim($from), $matches)) {
            return [trim($matches[1], " \""), trim($matches[2])];
        }

        return [$from, $from];
    }

    protected function extractBody(array $payload): string
    {
        if (isset($payload['body']['data']) && ($payload['body']['size'] ?? 0) > 0) {
            return $this->base64UrlDecode($payload['body']['data']);
        }

        foreach ($payload['parts'] ?? [] as $part) {
            if ($part['mimeType'] === 'text/html' && isset($part['body']['data'])) {
                return $this->base64UrlDecode($part['body']['data']);
            }
        }

        foreach ($payload['parts'] ?? [] as $part) {
            if ($part['mimeType'] === 'text/plain' && isset($part['body']['data'])) {
                return nl2br(e($this->base64UrlDecode($part['body']['data'])));
            }
        }

        foreach ($payload['parts'] ?? [] as $part) {
            $nested = $this->extractBody($part);
            if ($nested !== '') {
                return $nested;
            }
        }

        return '';
    }

    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Sends a real reply through the connected Gmail account, threaded onto
     * the original conversation via References/In-Reply-To when possible.
     */
    public function sendReply(EmailConversation $conversation, string $bodyHtml): void
    {
        $account = GmailAccount::current();
        $token = $this->accessToken($account);

        $lastIncoming = $conversation->messages()->where('direction', 'incoming')->latest('sent_at')->first();
        $replyToHeader = $lastIncoming?->message_id_header;

        $subject = Str::startsWith($conversation->subject, 'Re:') ? $conversation->subject : 'Re: '.$conversation->subject;

        $headers = [
            'To: '.$conversation->sender_email,
            'From: '.$account->email,
            'Subject: '.$subject,
            'Content-Type: text/html; charset=UTF-8',
            'MIME-Version: 1.0',
        ];

        if ($replyToHeader) {
            $headers[] = 'In-Reply-To: '.$replyToHeader;
            $headers[] = 'References: '.$replyToHeader;
        }

        $raw = implode("\r\n", $headers)."\r\n\r\n".$bodyHtml;

        $payload = ['raw' => $this->base64UrlEncode($raw)];

        if ($conversation->gmail_thread_id) {
            $payload['threadId'] = $conversation->gmail_thread_id;
        }

        $response = Http::withToken($token)->post(self::API_BASE.'/messages/send', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Gmail declined to send the reply (HTTP '.$response->status().'): '.$response->body());
        }
    }
}
