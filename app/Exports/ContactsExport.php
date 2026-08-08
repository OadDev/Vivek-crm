<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Contact::orderBy('company')->get()->map(fn (Contact $c) => [
            'Company Name' => $c->company,
            'Date' => optional($c->quotation_date)->format('Y-m-d'),
            'Phone No.' => $c->whatsapp,
            'Mail' => $c->email,
            'Sales Man' => $c->sales_man,
            'Address' => $c->shipping_address,
            'GST' => $c->gst_number,
            'Transport' => $c->transport,
            'Shipping Address' => $c->shipping_address,
            'Stage' => $c->stage,
            'Quote No.' => $c->quote_no,
            'Priority' => $c->priority,
            'Contact Person' => $c->name,
            'Designation' => $c->designation,
            'Status' => Contact::statusOptions()[$c->status] ?? $c->status,
            'Won' => $c->is_won ? 'Yes' : 'No',
            'Archived' => $c->is_archived ? 'Yes' : 'No',
            'Notes' => $c->notes,
        ]);
    }

    public function headings(): array
    {
        return [
            'Company Name', 'Date', 'Phone No.', 'Mail', 'Sales Man', 'Address', 'GST', 'Transport',
            'Shipping Address', 'Stage', 'Quote No.', 'Priority', 'Contact Person', 'Designation',
            'Status', 'Won', 'Archived', 'Notes',
        ];
    }
}
