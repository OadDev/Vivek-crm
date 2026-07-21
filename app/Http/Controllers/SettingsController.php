<?php

namespace App\Http\Controllers;

use App\Models\ContactSyncSetting;
use App\Models\Setting;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $templates = WhatsappTemplate::orderBy('name')->get();
        $syncSetting = ContactSyncSetting::current();

        $settings = [
            'gmail_connected' => Setting::get('gmail_connected', '1') === '1',
            'gmail_account' => Setting::get('gmail_account', auth()->user()->email),
            'whatsapp_sender_number' => Setting::get('whatsapp_sender_number', ''),
            'whatsapp_default_template_id' => Setting::get('whatsapp_default_template_id'),
            'pref_email_notifications' => Setting::get('pref_email_notifications', '1') === '1',
            'pref_whatsapp_alerts' => Setting::get('pref_whatsapp_alerts', '1') === '1',
            'pref_auto_archive' => Setting::get('pref_auto_archive', '0') === '1',
            'language' => Setting::get('language', 'English'),
            'timezone' => Setting::get('timezone', 'Asia/Kolkata (IST)'),
        ];

        return view('settings.index', compact('templates', 'settings', 'syncSetting'));
    }

    public function updateProfile(Request $request)
    {
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

    public function updateGmail(Request $request)
    {
        $connect = $request->input('action') === 'connect';

        Setting::setMany([
            'gmail_connected' => $connect ? '1' : '0',
            'gmail_account' => $connect ? auth()->user()->email : '',
        ]);

        return redirect()->route('settings.index')->with(
            'success',
            $connect ? 'Gmail account connected.' : 'Gmail account disconnected.'
        );
    }

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
