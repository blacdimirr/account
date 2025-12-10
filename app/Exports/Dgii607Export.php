<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii607Export implements FromCollection, WithHeadings
{
    public function __construct(private int $month, private int $year, private int $creatorId)
    {
    private int $month;
    private int $year;
    private int $creatorId;

    public function __construct(int $month, int $year, int $creatorId)
    {
        $this->month = $month;
        $this->year = $year;
        $this->creatorId = $creatorId;
    }

    public function collection(): Collection
    {
        $invoices = Invoice::with('customer', 'ncfType', 'items')
        $invoices = Invoice::with(['customer', 'ncfType', 'items'])
            ->where('created_by', $this->creatorId)
            ->whereYear('issue_date', $this->year)
            ->whereMonth('issue_date', $this->month)
            ->get();

        return $invoices->map(function (Invoice $invoice) {
            $taxNumber = optional($invoice->customer)->tax_number;
            $idType = strlen((string) $taxNumber) === 11 ? 'Cedula' : 'RNC';
            $baseAmount = $invoice->getSubTotal() - $invoice->getTotalDiscount();

            return [
                $invoice->issue_date,
                optional($invoice->customer)->name,
                $taxNumber,
                $idType,
                optional($invoice->ncfType)->code ?? '',
                $invoice->ncf_number ?? '',
                round($baseAmount, 2),
                $invoice->getTotal(),
                round($invoice->getTotalTax(), 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Cliente',
            'Identificacion',
            'Tipo Identificacion',
            'Tipo NCF',
            'NCF',
            'Base Imponible',
            'Monto Facturado',
            'ITBIS Facturado',
        ];
    }
}
}
