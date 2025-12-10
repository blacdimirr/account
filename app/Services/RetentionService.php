<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\RetentionRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RetentionService
{
    public function buildBillRetentions(Bill $bill): Collection
    {
        $retentions = collect();
        $baseAmount = max(0, ($bill->getSubTotal() - $bill->getTotalDiscount()));
        $taxAmount = max(0, $bill->getTotalTax());

        $itbisRate = (float) config('dgii.itbis_retention_rate', 0);
        if ($taxAmount > 0 && $itbisRate > 0) {
            $retentions->push($this->makeRecordPayload($bill, 'itbis', $baseAmount, $taxAmount, $itbisRate));
        }

        $isrRate = (float) config('dgii.isr_retention_rate', 0);
        if ($baseAmount > 0 && $isrRate > 0) {
            $retentions->push($this->makeRecordPayload($bill, 'isr', $baseAmount, 0, $isrRate));
        }

        return $retentions;
    }

    public function storeBillRetentions(Bill $bill): void
    {
        if (! $bill->has_retention) {
            return;
        }

        $this->persist($this->buildBillRetentions($bill));
    }

    public function storePaymentRetentions(BillPayment $payment): void
    {
        $bill = $payment->bill;

        if (! $bill || ! $bill->has_retention) {
            return;
        }

        $billTotal = max(1, $bill->getTotal());
        $paymentRatio = max(0, min(1, $payment->amount / $billTotal));

        $retentions = $this->buildBillRetentions($bill)->map(function ($payload) use ($paymentRatio, $payment) {
            $payload['document_type'] = 'bill_payment';
            $payload['document_id'] = $payment->id;
            $payload['base_amount'] = round($payload['base_amount'] * $paymentRatio, 2);
            $payload['tax_amount'] = round($payload['tax_amount'] * $paymentRatio, 2);
            $payload['retained_amount'] = round($payload['retained_amount'] * $paymentRatio, 2);

            return $payload;
        });

        $this->persist($retentions);
    }

    protected function persist(Collection $payloads): void
    {
        foreach ($payloads as $payload) {
            RetentionRecord::create($payload);
        }
    }

    protected function makeRecordPayload(Bill $bill, string $type, float $baseAmount, float $taxAmount, float $rate): array
    {
        $retainedAmount = $type === 'itbis' ? $taxAmount * $rate : $baseAmount * $rate;
        $date = Carbon::parse($bill->bill_date ?? now());

        return [
            'document_type' => 'bill',
            'document_id' => $bill->id,
            'retention_type' => $type,
            'base_amount' => round($baseAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'retained_amount' => round($retainedAmount, 2),
            'rate' => $rate,
            'ncf_number' => $bill->ncf_number,
            'period_month' => (int) $date->format('m'),
            'period_year' => (int) $date->format('Y'),
            'created_by' => $bill->created_by,
        ];
    }
}
