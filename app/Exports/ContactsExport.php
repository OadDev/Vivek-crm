<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Contact::orderBy('name')->get()->map(fn (Contact $c) => [
            'Name' => $c->name,
            'Company' => $c->company,
            'Email' => $c->email,
            'WhatsApp' => $c->whatsapp,
            'Designation' => $c->designation,
            'Status' => Contact::statusOptions()[$c->status] ?? $c->status,
            'Last Contacted' => optional($c->last_contacted_at)->format('Y-m-d'),
            'Notes' => $c->notes,
        ]);
    }

    public function headings(): array
    {
        return ['Name', 'Company', 'Email', 'WhatsApp', 'Designation', 'Status', 'Last Contacted', 'Notes'];
    }
}
