<?php

namespace App\Http\Controllers;

use App\Exports\ContactsExport;
use App\Imports\ContactsImport;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\ContactSyncSetting;
use App\Models\Reminder;
use App\Models\Setting;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Services\ContactSyncService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    protected const SORTABLE = ['name', 'company', 'quote_no', 'quotation_date', 'priority', 'status', 'email', 'whatsapp', 'last_contacted_at', 'created_at'];

    protected const PER_PAGE_OPTIONS = [10, 20, 50, 100];

    public function index(Request $request)
    {
        $query = Contact::query();

        $view = in_array($request->input('view'), ['pipeline', 'won', 'archived', 'all'], true)
            ? $request->input('view')
            : 'pipeline';

        match ($view) {
            'won' => $query->won(),
            'archived' => $query->archived(),
            'all' => null,
            default => $query->pipeline(),
        };

        $user = auth()->user();
        if (! $user->isAdmin() && $user->sales_man) {
            $query->where('sales_man', $user->sales_man);
        }

        // Scoped by view + role only (no search/other filters yet) so the
        // status-tab counts reflect "how many are in this pipeline" rather
        // than shrinking to match whatever the user is currently typing.
        $statusScopeQuery = clone $query;

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('quote_no', 'like', "%{$search}%");
            });
        }

        foreach (['name', 'company', 'email', 'whatsapp', 'designation'] as $field) {
            if ($request->filled("filter_{$field}")) {
                $query->where($field, 'like', '%'.$request->input("filter_{$field}").'%');
            }
        }

        // A fresh visit (no filter_status in the URL at all) defaults to
        // Active leads, not every status. The "All" chip passes the explicit
        // sentinel value 'all' so it can still mean "no status filter" --
        // distinct from "not chosen yet".
        $filterStatus = $request->input('filter_status');
        if ($filterStatus === null) {
            $filterStatus = 'active';
        } elseif ($filterStatus === 'all') {
            $filterStatus = null;
        }

        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('quotation_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('quotation_date', '<=', $request->input('date_to'));
        }

        if ($request->boolean('starred_only')) {
            $query->where('is_starred', true);
        }

        $sort = in_array($request->input('sort'), self::SORTABLE, true) ? $request->input('sort') : 'quotation_date';
        $dir = $request->input('dir') ?: ($request->filled('sort') ? 'asc' : 'desc');
        $dir = $dir === 'desc' ? 'desc' : 'asc';

        $perPage = in_array((int) $request->input('per_page'), self::PER_PAGE_OPTIONS, true)
            ? (int) $request->input('per_page')
            : 20;

        // Same-name leads with multiple Quote Nos. are grouped into one row
        // (most recent quotation shown, with an expandable panel for the
        // rest) instead of paginating raw rows, so pagination happens on
        // groups. Trimmed to the columns the list view actually renders.
        $all = $query->starredFirst()->orderBy($sort, $dir)->get([
            'id', 'quote_no', 'quotation_date', 'name', 'company', 'email', 'whatsapp',
            'designation', 'sales_man', 'gst_number', 'transport', 'shipping_address',
            'stage', 'priority', 'status', 'is_starred', 'is_archived', 'is_won', 'notes',
        ]);

        $groups = $all->groupBy(fn (Contact $c) => $c->company ?: $c->name)->map(function ($items) {
            $primary = $items->sortByDesc(fn (Contact $c) => optional($c->quotation_date)->timestamp ?? 0)->first();

            return (object) [
                'primary' => $primary,
                'others' => $items->reject(fn (Contact $c) => $c->id === $primary->id)->values(),
                'count' => $items->count(),
            ];
        })->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $contacts = new LengthAwarePaginator(
            $groups->slice(($page - 1) * $perPage, $perPage)->values(),
            $groups->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $syncSetting = ContactSyncSetting::current();

        $counts = [
            'pipeline' => Contact::query()->pipeline()->count(),
            'won' => Contact::query()->won()->count(),
            'archived' => Contact::query()->archived()->count(),
        ];

        $statusCounts = (clone $statusScopeQuery)->selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $counts['all_statuses'] = (clone $statusScopeQuery)->count();

        $effectiveFilterStatus = $filterStatus ?: 'all';

        if ($request->ajax()) {
            return view('contacts._table', compact('contacts', 'sort', 'dir', 'perPage'));
        }

        return view('contacts.index', compact('contacts', 'sort', 'dir', 'perPage', 'syncSetting', 'view', 'counts', 'statusCounts', 'effectiveFilterStatus'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['name'] = $data['name'] ?: $data['company'];

        $contact = Contact::create($data + [
            'source' => 'manual',
            'last_contacted_at' => $data['last_contacted_at'] ?? now(),
        ]);

        Activity::log("New contact <b>{$contact->name}</b> added manually", 'bi-person-plus-fill', 'success', $contact);

        return redirect()->route('contacts.index')->with('success', 'Contact added successfully.');
    }

    public function show(Contact $contact)
    {
        $contact->load(['emailConversations.messages', 'whatsappMessages.template']);

        return view('contacts.show', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $this->validated($request, $contact);
        $data['name'] = $data['name'] ?: $data['company'];
        $contact->update($data);

        Activity::log("Contact <b>{$contact->name}</b> updated", 'bi-pencil-fill', 'primary', $contact);

        return redirect()->back()->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $name = $contact->name;
        $contact->delete();

        Activity::log("Contact <b>{$name}</b> deleted", 'bi-trash-fill', 'danger');

        return redirect()->route('contacts.index')->with('success', 'Contact deleted.');
    }

    public function toggleStar(Contact $contact)
    {
        $contact->update(['is_starred' => ! $contact->is_starred]);

        return redirect()->back();
    }

    public function archive(Contact $contact)
    {
        $contact->update(['is_archived' => true, 'archived_at' => now()]);

        Activity::log("Lead <b>{$contact->name}</b> archived", 'bi-archive-fill', 'warning', $contact);

        return redirect()->back()->with('success', 'Lead archived and removed from the pipeline.');
    }

    public function unarchive(Contact $contact)
    {
        $contact->update(['is_archived' => false, 'archived_at' => null]);

        Activity::log("Lead <b>{$contact->name}</b> restored from archive", 'bi-archive', 'info', $contact);

        return redirect()->back()->with('success', 'Lead restored to the pipeline.');
    }

    public function markWon(Contact $contact)
    {
        $contact->update(['is_won' => true, 'won_at' => now()]);

        Activity::log("Quotation <b>{$contact->quote_no}</b> for <b>{$contact->name}</b> marked Won", 'bi-trophy-fill', 'success', $contact);

        return redirect()->back()->with('success', 'Marked as Won.');
    }

    public function unmarkWon(Contact $contact)
    {
        $contact->update(['is_won' => false, 'won_at' => null]);

        Activity::log("Quotation <b>{$contact->quote_no}</b> for <b>{$contact->name}</b> reverted from Won", 'bi-trophy', 'info', $contact);

        return redirect()->back()->with('success', 'Reverted from Won.');
    }

    /**
     * One-click WhatsApp: render the sender's personal default template if
     * they've set one, else the company default, and hand off straight to
     * whatsapp:// (falling back to wa.me client-side) — no picker.
     */
    public function whatsapp(Contact $contact)
    {
        if (! $contact->whatsapp) {
            return redirect()->back()->with('error', 'This contact has no WhatsApp number.');
        }

        $user = auth()->user();
        $templateId = $user->whatsapp_default_template_id ?: Setting::get('whatsapp_default_template_id');
        $template = $templateId ? WhatsappTemplate::find($templateId) : null;

        $message = $template
            ? $template->render(['name' => $contact->name, 'company' => $contact->company])
            : '';

        $whatsappMessage = WhatsappMessage::create([
            'contact_id' => $contact->id,
            'whatsapp_template_id' => $template?->id,
            'recipient_name' => $contact->name,
            'recipient_number' => $contact->whatsapp,
            'message' => $message,
            'sent_at' => now(),
        ]);

        $contact->update(['last_contacted_at' => now()]);

        Activity::log("WhatsApp opened for <b>{$contact->name}</b>", 'bi-whatsapp', 'success', $contact);

        // The Contacts list fetches this in the background (see the
        // whatsapp click handler in index.blade.php) so it can try
        // whatsapp:// without ever navigating the page away -- only the
        // full-page view below is used as a plain-link fallback (e.g. if
        // JS is disabled, or opened directly).
        if (request()->wantsJson()) {
            return response()->json([
                'appLink' => $whatsappMessage->waAppLink(),
                'webLink' => $whatsappMessage->waLink(),
            ]);
        }

        return view('contacts.whatsapp-redirect', [
            'appLink' => $whatsappMessage->waAppLink(),
            'webLink' => $whatsappMessage->waLink(),
        ]);
    }

    public function remind(Request $request, Contact $contact)
    {
        $days = (int) $request->input('days');
        abort_unless(in_array($days, [2, 7], true), 422);

        Reminder::create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id(),
            'remind_at' => now()->addDays($days),
        ]);

        Activity::log("Reminder set for <b>{$contact->name}</b> in {$days} day(s)", 'bi-alarm-fill', 'warning', $contact);

        return redirect()->back()->with('success', "You'll be reminded about {$contact->name} in {$days} day(s).");
    }

    public function importForm()
    {
        return view('contacts.import');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $import = new ContactsImport('excel_import');
        Excel::import($import, $request->file('file'));

        Activity::log(
            "Contacts imported from Excel: {$import->result['created']} created, {$import->result['updated']} updated",
            'bi-file-earmark-spreadsheet-fill',
            'info'
        );

        return redirect()->route('contacts.index')->with(
            'success',
            "Import complete — {$import->result['created']} created, {$import->result['updated']} updated, {$import->result['skipped']} skipped."
        );
    }

    public function export()
    {
        return Excel::download(new ContactsExport, 'contacts_export_'.now()->format('Ymd_His').'.xlsx');
    }

    public function syncSettingsUpdate(Request $request)
    {
        $data = $request->validate([
            'source_type' => ['required', 'in:excel_upload,google_sheet'],
            'google_sheet_url' => ['nullable', 'url'],
            'interval_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'is_enabled' => ['nullable', 'boolean'],
            'sync_file' => ['nullable', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $setting = ContactSyncSetting::current();

        if ($request->hasFile('sync_file')) {
            $path = $request->file('sync_file')->store('contact-sync');
            $data['excel_file_path'] = $path;
            $data['excel_original_name'] = $request->file('sync_file')->getClientOriginalName();
        }

        $data['is_enabled'] = $request->boolean('is_enabled');

        $setting->update($data);

        return redirect()->back()->with('success', 'Data source settings saved.');
    }

    public function syncNow(ContactSyncService $service)
    {
        $result = $service->run(force: true);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    protected function validated(Request $request, ?Contact $contact = null): array
    {
        return $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'designation' => ['nullable', 'string', 'max:255'],
            'quote_no' => ['nullable', 'string', 'max:100', 'unique:contacts,quote_no'.($contact ? ','.$contact->id : '')],
            'quotation_date' => ['nullable', 'date'],
            'sales_man' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'transport' => ['nullable', 'string', 'max:255'],
            'shipping_address' => ['nullable', 'string'],
            'stage' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,follow_up,inactive'],
            'last_contacted_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
