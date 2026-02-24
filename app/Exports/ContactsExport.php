<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return auth()->user()->contacts;
    }

    public function headings(): array
    {
        return [
            'الاسم',
            'الهاتف',
        ];
    }

    /**
     * @param mixed $contact
     * @return array
     */
    public function map($contact): array
    {
        return [
            $contact->name,
            $contact->phone,
        ];
    }
}
