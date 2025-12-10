<?php

namespace App\Exports;

use App\Models\Bill;
use App\Models\RetentionRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii606Export implements FromCollection, WithHeadings
{
    public function __construct(private int $month, private int $year, private int $creatorId)
    public function __construct(private int $month, private int $year)
    {
    }

    public function collection(): Collection
    {
        $bills = Bill::with('vender', 'ncfType', 'payments', 'items')
            ->where('created_by', $this->creatorId)
        $bills = Bill::with('vender', 'ncfType')
            ->whereYear('bill_date', $this->year)
            ->whereMonth('bill_date', $this->month)
            ->get();

        return $bills->map(function (Bill $bill) {
            $retentions = RetentionRecord::where('period_year', $this->year)
                ->where('period_month', $this->month)
                ->where(function ($query) use ($bill) {
                    $query->where(function ($query) use ($bill) {
                        $query->where('document_type', 'bill')
                            ->where('document_id', $bill->id);
                    });

                    $paymentIds = $bill->payments()->pluck('id');
                    if ($paymentIds->isNotEmpty()) {
                        $query->orWhere(function ($query) use ($paymentIds) {
                            $query->where('document_type', 'bill_payment')
                                ->whereIn('document_id', $paymentIds);
                        });
                    }
                })
                ->get();

            $taxNumber = optional($bill->vender)->tax_number;
            $idType = strlen((string) $taxNumber) === 11 ? 'Cedula' : 'RNC';
            $baseAmount = $bill->getSubTotal() - $bill->getTotalDiscount();

            return [
                $bill->bill_date,
                optional($bill->vender)->name,
                $taxNumber,
                $idType,
                optional($bill->ncfType)->code ?? '',
                $bill->ncf_number ?? '',
                round($baseAmount, 2),
                $bill->getTotal(),
                round($bill->getTotalTax(), 2),
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
            'Tipo Identificacion',
            'Tipo NCF',
            'NCF',
            'Base Imponible',
            'Tipo NCF',
            'NCF',
            'Monto Facturado',
            'ITBIS Facturado',
            'ITBIS Retenido',
            'ISR Retenido',
        ];
    }
}
