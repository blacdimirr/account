<?php

namespace App\Exports;

use App\Models\Bill;
use App\Models\RetentionRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii606Export implements FromCollection, WithHeadings
{
    public function __construct(private int $month, private int $year)
    {
    }

    public function collection(): Collection
    {
        $bills = Bill::with('vender', 'ncfType')
            ->whereYear('bill_date', $this->year)
            ->whereMonth('bill_date', $this->month)
            ->get();

        return $bills->map(function (Bill $bill) {
            $retentions = RetentionRecord::where('document_type', 'bill')
                ->where('document_id', $bill->id)
                ->get();

            return [
                $bill->bill_date,
                optional($bill->vender)->name,
                optional($bill->vender)->billing_phone,
                optional($bill->ncfType)->code ?? '',
                $bill->ncf_number ?? '',
                $bill->getTotal(),
                $bill->getTotalTax(),
                (float) $retentions->where('retention_type', 'itbis')->sum('retained_amount'),
                (float) $retentions->where('retention_type', 'isr')->sum('retained_amount'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Proveedor',
            'Identificacion',
            'Tipo NCF',
            'NCF',
            'Monto Facturado',
            'ITBIS Facturado',
            'ITBIS Retenido',
            'ISR Retenido',
        ];
    }
}
