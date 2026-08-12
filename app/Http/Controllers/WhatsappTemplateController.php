<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;

class WhatsappTemplateController extends Controller
{
    /**
     * Any user can create their own personal template. Only an admin can
     * create a shared/company one (no user_id posted).
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $ownerId = auth()->user()->isAdmin() && $request->boolean('is_shared')
            ? null
            : auth()->id();

        $template = WhatsappTemplate::create($data + ['user_id' => $ownerId]);

        Activity::log("WhatsApp template <b>{$template->name}</b> created", 'bi-whatsapp', 'success', $template);

        return redirect()->route('settings.index')->with('success', 'Template created successfully.');
    }

    public function update(Request $request, WhatsappTemplate $whatsappTemplate)
    {
        $this->authorizeOwnership($whatsappTemplate);

        $data = $this->validated($request);
        $whatsappTemplate->update($data);

        Activity::log("WhatsApp template <b>{$whatsappTemplate->name}</b> updated", 'bi-whatsapp', 'primary', $whatsappTemplate);

        return redirect()->route('settings.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(WhatsappTemplate $whatsappTemplate)
    {
        $this->authorizeOwnership($whatsappTemplate);

        $name = $whatsappTemplate->name;
        $whatsappTemplate->delete();

        Activity::log("WhatsApp template <b>{$name}</b> deleted", 'bi-trash-fill', 'danger');

        return redirect()->route('settings.index')->with('success', 'Template deleted.');
    }

    protected function authorizeOwnership(WhatsappTemplate $template): void
    {
        $isOwnTemplate = $template->user_id === auth()->id();
        $isSharedAndAdmin = $template->isShared() && auth()->user()->isAdmin();

        if (! $isOwnTemplate && ! $isSharedAndAdmin) {
            abort(403);
        }
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);
    }
}
