<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;

class WhatsappTemplateController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $template = WhatsappTemplate::create($data);

        Activity::log("WhatsApp template <b>{$template->name}</b> created", 'bi-whatsapp', 'success', $template);

        return redirect()->route('settings.index')->with('success', 'Template created successfully.');
    }

    public function update(Request $request, WhatsappTemplate $whatsappTemplate)
    {
        $data = $this->validated($request);
        $whatsappTemplate->update($data);

        Activity::log("WhatsApp template <b>{$whatsappTemplate->name}</b> updated", 'bi-whatsapp', 'primary', $whatsappTemplate);

        return redirect()->route('settings.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(WhatsappTemplate $whatsappTemplate)
    {
        $name = $whatsappTemplate->name;
        $whatsappTemplate->delete();

        Activity::log("WhatsApp template <b>{$name}</b> deleted", 'bi-trash-fill', 'danger');

        return redirect()->route('settings.index')->with('success', 'Template deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);
    }
}
