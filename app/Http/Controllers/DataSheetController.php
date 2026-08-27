<?php

namespace App\Http\Controllers;

use App\Models\DataSheet;
use App\Services\DataSheetSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataSheetController extends Controller
{
    protected const PER_PAGE_OPTIONS = [10, 20, 50, 100];

    public function index()
    {
        $dataSheets = DataSheet::withCount('rows')->orderBy('name')->get();

        return view('data-sheets.index', compact('dataSheets'));
    }

    public function store(Request $request, DataSheetSyncService $service)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'source_type' => ['required', 'in:google_sheet,excel_upload'],
            'google_sheet_url' => ['nullable', 'url'],
            'sync_file' => ['nullable', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        if ($request->hasFile('sync_file')) {
            $data['excel_file_path'] = $request->file('sync_file')->store('data-sheets');
            $data['excel_original_name'] = $request->file('sync_file')->getClientOriginalName();
        }

        $sheet = DataSheet::create($data + ['created_by' => auth()->id()]);

        $result = $service->sync($sheet);

        return redirect()->route('data-sheets.index')->with(
            $result['success'] ? 'success' : 'error',
            "Data sheet \"{$sheet->name}\" created. ".$result['message']
        );
    }

    public function update(Request $request, DataSheet $dataSheet)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'source_type' => ['required', 'in:google_sheet,excel_upload'],
            'google_sheet_url' => ['nullable', 'url'],
            'sync_file' => ['nullable', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        if ($request->hasFile('sync_file')) {
            if ($dataSheet->excel_file_path) {
                Storage::disk('local')->delete($dataSheet->excel_file_path);
            }
            $data['excel_file_path'] = $request->file('sync_file')->store('data-sheets');
            $data['excel_original_name'] = $request->file('sync_file')->getClientOriginalName();
        }

        $dataSheet->update($data);

        return redirect()->route('data-sheets.index')->with('success', 'Data sheet updated.');
    }

    public function destroy(DataSheet $dataSheet)
    {
        if ($dataSheet->excel_file_path) {
            Storage::disk('local')->delete($dataSheet->excel_file_path);
        }

        $name = $dataSheet->name;
        $dataSheet->delete();

        return redirect()->route('data-sheets.index')->with('success', "\"{$name}\" removed.");
    }

    public function syncNow(DataSheet $dataSheet, DataSheetSyncService $service)
    {
        $result = $service->sync($dataSheet);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function show(Request $request, DataSheet $dataSheet)
    {
        $headers = $dataSheet->headers ?? [];

        $query = $dataSheet->rows();

        if ($request->filled('search')) {
            $query->where('search_text', 'like', '%'.strtolower($request->string('search')).'%');
        }

        $sort = $request->input('sort');
        $dir = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        if ($sort && in_array($sort, $headers, true)) {
            $driver = DB::connection()->getDriverName();
            $path = '$."'.str_replace('"', '', $sort).'"';

            $extract = $driver === 'sqlite'
                ? 'json_extract(data, ?)'
                : 'JSON_UNQUOTE(JSON_EXTRACT(data, ?))';

            $query->orderByRaw("{$extract} {$dir}", [$path]);
        } else {
            $query->orderBy('sort_order');
        }

        $perPage = in_array((int) $request->input('per_page'), self::PER_PAGE_OPTIONS, true)
            ? (int) $request->input('per_page')
            : 20;

        $rows = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('data-sheets._table', compact('dataSheet', 'headers', 'rows', 'sort', 'dir', 'perPage'));
        }

        return view('data-sheets.show', compact('dataSheet', 'headers', 'rows', 'sort', 'dir', 'perPage'));
    }
}
