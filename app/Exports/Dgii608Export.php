<?php

namespace App\Exports;

use App\Models\CreditNote;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii608Export implements FromCollection, WithHeadings
{
    public function __construct(private int $month, private int $year, private int $creatorId)
    public function __construct(private int $month, private int $year)
    {
    }

    public function collection(): Collection
    {
        $notes = CreditNote::with('invoice.customer')
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->whereHas('invoice', function ($query) {
                $query->where('created_by', $this->creatorId);
            })
            ->get();

        return $notes->map(function (CreditNote $note) {
            $invoice = $note->invoice;
            $customer = $invoice?->customer;

            return [
                $note->date,
                $invoice?->ncf_number,
                $note->amount,
                optional($customer)->name,
                optional($customer)->tax_number,
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
            'NCF Factura relacionada',
            'Monto Nota',
            'Cliente',
            'Identificacion Cliente',
            'Factura relacionada',
            'Monto Nota',
            'Cliente',
        ];
    }
}
