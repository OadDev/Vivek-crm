<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;

class WhatsappTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = WhatsappTemplate::query()
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('name', 'like', "%{$search}%")->orWhere('message', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->get();

        $contacts = Contact::orderBy('name')->get(['id', 'name', 'whatsapp']);

        return view('whatsapp.index', compact('templates', 'contacts'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $template = WhatsappTemplate::create($data);

        Activity::log("WhatsApp template <b>{$template->name}</b> created", 'bi-whatsapp', 'success', $template);

        return redirect()->route('whatsapp.index')->with('success', 'Template created successfully.');
    }

    public function update(Request $request, WhatsappTemplate $whatsappTemplate)
    {
        $data = $this->validated($request);
        $whatsappTemplate->update($data);

        Activity::log("WhatsApp template <b>{$whatsappTemplate->name}</b> updated", 'bi-whatsapp', 'primary', $whatsappTemplate);

        return redirect()->route('whatsapp.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(WhatsappTemplate $whatsappTemplate)
    {
        $name = $whatsappTemplate->name;
        $whatsappTemplate->delete();

        Activity::log("WhatsApp template <b>{$name}</b> deleted", 'bi-trash-fill', 'danger');

        return redirect()->route('whatsapp.index')->with('success', 'Template deleted.');
    }

    /**
     * Render a template for a chosen recipient (contact or manual number),
     * log the outgoing message, and hand back a real wa.me deep link.
     */
    public function send(Request $request)
    {
        $data = $request->validate([
            'template_id' => ['nullable', 'exists:whatsapp_templates,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'recipient_name' => ['required_without:contact_id', 'nullable', 'string', 'max:255'],
            'recipient_number' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string'],
        ]);

        $contact = ($data['contact_id'] ?? null) ? Contact::find($data['contact_id']) : null;

        $whatsappMessage = WhatsappMessage::create([
            'contact_id' => $contact?->id,
            'whatsapp_template_id' => $data['template_id'] ?? null,
            'recipient_name' => $contact->name ?? $data['recipient_name'],
            'recipient_number' => $data['recipient_number'],
            'message' => $data['message'],
            'sent_at' => now(),
        ]);

        if ($contact) {
            $contact->update(['last_contacted_at' => now(), 'status' => 'active']);
        }

        Activity::log("WhatsApp message opened for <b>{$whatsappMessage->recipient_name}</b>", 'bi-whatsapp', 'success', $whatsappMessage);

        return response()->json([
            'success' => true,
            'wa_link' => $whatsappMessage->waLink(),
        ]);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);
    }
}
