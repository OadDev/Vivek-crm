<?php

namespace App\Http\Controllers;

use App\Models\GmailAccount;
use App\Services\GmailApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GmailAuthController extends Controller
{
    protected const SCOPES = [
        'openid',
        'email',
        'profile',
        'https://www.googleapis.com/auth/gmail.modify',
        'https://www.googleapis.com/auth/gmail.send',
    ];

    public function saveCredentials(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:255'],
        ]);

        $account = GmailAccount::current();

        $account->update([
            'client_id' => $data['client_id'],
            // Blank means "keep the existing secret" -- the field is never
            // re-populated with the real value once saved, so an empty
            // submit isn't the user intentionally clearing it.
            'client_secret' => $data['client_secret'] !== null && $data['client_secret'] !== ''
                ? $data['client_secret']
                : $account->client_secret,
        ]);

        return redirect()->route('settings.index')->with('success', 'Google OAuth credentials saved.');
    }

    protected function configureSocialite(): void
    {
        $account = GmailAccount::current();

        config([
            'services.google.client_id' => $account->resolvedClientId(),
            'services.google.client_secret' => $account->resolvedClientSecret(),
            'services.google.redirect' => route('settings.gmail.callback'),
        ]);
    }

    public function redirect()
    {
        if (! GmailAccount::current()->hasCredentials()) {
            return redirect()->route('settings.index')->with('error', 'Add your Google OAuth Client ID and Secret first (see the instructions button).');
        }

        $this->configureSocialite();

        return Socialite::driver('google')
            ->scopes(self::SCOPES)
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function callback()
    {
        $this->configureSocialite();

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Gmail OAuth callback failed: '.$e->getMessage());

            return redirect()->route('settings.index')->with('error', 'Gmail connection failed: '.$e->getMessage());
        }

        if (! $googleUser->refreshToken) {
            return redirect()->route('settings.index')->with(
                'error',
                'Google did not return a refresh token. Disconnect any existing app access at myaccount.google.com/permissions and try connecting again.'
            );
        }

        GmailAccount::current()->update([
            'google_id' => $googleUser->getId(),
            'email' => $googleUser->getEmail(),
            'name' => $googleUser->getName(),
            'access_token' => $googleUser->token,
            'refresh_token' => $googleUser->refreshToken,
            'token_expires_at' => now()->addSeconds($googleUser->expiresIn ?? 3600),
            'connected_at' => now(),
        ]);

        return redirect()->route('settings.index')->with('success', 'Gmail account connected: '.$googleUser->getEmail());
    }

    public function disconnect()
    {
        GmailAccount::current()->disconnect();

        return redirect()->route('settings.index')->with('success', 'Gmail account disconnected.');
    }

    public function syncNow(GmailApiService $service)
    {
        $account = GmailAccount::current();

        if (! $account->isConnected()) {
            return redirect()->route('settings.index')->with('error', 'Connect a Gmail account first.');
        }

        try {
            $result = $service->syncInbox();

            return redirect()->route('settings.index')->with(
                'success',
                "Gmail sync complete — {$result['created']} new message(s), {$result['skipped']} already up to date."
            );
        } catch (Throwable $e) {
            $account->update(['last_sync_status' => 'failed', 'last_sync_message' => $e->getMessage()]);

            return redirect()->route('settings.index')->with('error', 'Gmail sync failed: '.$e->getMessage());
        }
    }
}
