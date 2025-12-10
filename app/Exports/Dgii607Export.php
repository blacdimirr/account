<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii607Export implements FromCollection, WithHeadings
{
    public function __construct(private int $month, private int $year)
    {
    }

    public function collection(): Collection
    {
        $invoices = Invoice::with('customer', 'ncfType')
            ->whereYear('issue_date', $this->year)
            ->whereMonth('issue_date', $this->month)
            ->get();

        return $invoices->map(function (Invoice $invoice) {
            return [
                $invoice->issue_date,
                optional($invoice->customer)->name,
                optional($invoice->customer)->contact,
                optional($invoice->ncfType)->code ?? '',
                $invoice->ncf_number ?? '',
                $invoice->getTotal(),
                $invoice->getTotalTax(),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Cliente',
            'Identificacion',
            'Tipo NCF',
            'NCF',
            'Monto Facturado',
            'ITBIS Facturado',
        ];
    }
}
