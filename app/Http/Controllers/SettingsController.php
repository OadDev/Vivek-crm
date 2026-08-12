<?php

namespace App\Http\Controllers;

use App\Models\ContactSyncSetting;
use App\Models\GmailAccount;
use App\Models\Setting;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $companyTemplates = WhatsappTemplate::whereNull('user_id')->orderBy('name')->get();
        $myTemplates = WhatsappTemplate::where('user_id', auth()->id())->orderBy('name')->get();
        $syncSetting = ContactSyncSetting::current();
        $gmailAccount = GmailAccount::forUser(auth()->user());
        $gmailSharedClient = GmailAccount::sharedClient();
        $gmailCallbackUrl = route('settings.gmail.callback');

        $settings = [
            'whatsapp_sender_number' => Setting::get('whatsapp_sender_number', ''),
            'whatsapp_default_template_id' => Setting::get('whatsapp_default_template_id'),
            'pref_email_notifications' => Setting::get('pref_email_notifications', '1') === '1',
            'pref_whatsapp_alerts' => Setting::get('pref_whatsapp_alerts', '1') === '1',
            'pref_auto_archive' => Setting::get('pref_auto_archive', '0') === '1',
            'language' => Setting::get('language', 'English'),
            'timezone' => Setting::get('timezone', 'Asia/Kolkata (IST)'),
        ];

        return view('settings.index', compact(
            'companyTemplates', 'myTemplates', 'settings', 'syncSetting',
            'gmailAccount', 'gmailSharedClient', 'gmailCallbackUrl'
        ));
    }

    public function updateProfile(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
            auth()->user()->update($data);

            return redirect()->route('settings.index')->with('success', 'Name updated. Ask your admin to change your email or phone.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.auth()->id()],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        auth()->user()->update($data);

        return redirect()->route('settings.index')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update(['password' => Hash::make($request->input('password'))]);

        return redirect()->route('settings.index')->with('success', 'Password updated successfully.');
    }

    public function updateSignature(Request $request)
    {
        $data = $request->validate(['html_signature' => ['nullable', 'string']]);

        auth()->user()->update($data);

        return redirect()->route('settings.index')->with('success', 'Email signature saved.');
    }

    /**
     * Each user's own default WhatsApp template — can be one of their
     * personal templates or a shared company one. Falls back to the
     * company-wide default (below) when unset.
     */
    public function updateWhatsappPersonal(Request $request)
    {
        $data = $request->validate(['whatsapp_default_template_id' => ['nullable', 'exists:whatsapp_templates,id']]);

        if ($data['whatsapp_default_template_id'] ?? null) {
            $template = WhatsappTemplate::findOrFail($data['whatsapp_default_template_id']);

            if ($template->user_id !== null && $template->user_id !== auth()->id()) {
                abort(403);
            }
        }

        auth()->user()->update($data);

        return redirect()->route('settings.index')->with('success', 'Your default WhatsApp template is saved.');
    }

    /**
     * Company-wide WhatsApp settings — admin only.
     */
    public function updateWhatsapp(Request $request)
    {
        $data = $request->validate([
            'whatsapp_sender_number' => ['nullable', 'string', 'max:30'],
            'whatsapp_default_template_id' => ['nullable', 'exists:whatsapp_templates,id'],
        ]);

        Setting::setMany($data);

        return redirect()->route('settings.index')->with('success', 'WhatsApp settings saved.');
    }

    public function updatePreferences(Request $request)
    {
        Setting::setMany([
            'pref_email_notifications' => $request->boolean('pref_email_notifications') ? '1' : '0',
            'pref_whatsapp_alerts' => $request->boolean('pref_whatsapp_alerts') ? '1' : '0',
            'pref_auto_archive' => $request->boolean('pref_auto_archive') ? '1' : '0',
            'language' => $request->input('language', 'English'),
            'timezone' => $request->input('timezone', 'Asia/Kolkata (IST)'),
        ]);

        return redirect()->route('settings.index')->with('success', 'Preferences saved.');
    }
}
