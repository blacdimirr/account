<?php

namespace App\Exports;

use App\Models\CreditNote;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii608Export implements FromCollection, WithHeadings
{
    public function __construct(private int $month, private int $year)
    {
    }

    public function collection(): Collection
    {
        $notes = CreditNote::whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->get();

        return $notes->map(function (CreditNote $note) {
            return [
                $note->date,
                $note->invoice,
                $note->amount,
                $note->customer,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Factura relacionada',
            'Monto Nota',
            'Cliente',
        ];
    }
}
