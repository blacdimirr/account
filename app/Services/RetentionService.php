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
        return $this->buildRetentions($bill, 'bill', $bill->id, Carbon::parse($bill->bill_date ?? now()));
    }

    public function storeBillRetentions(Bill $bill): void
    {
        if (! $bill->has_retention) {
            return;
        }

        $this->persist($this->buildBillRetentions($bill));
    }

    public function buildPaymentRetentions(BillPayment $payment): Collection
    {
        $bill = $payment->bill;

        if (! $bill || ! $bill->has_retention) {
            return collect();
        }

        $billTotal = max(1, $bill->getTotal());
        $paymentRatio = max(0, min(1, $payment->amount / $billTotal));
        $periodDate = Carbon::parse($payment->date ?? $bill->bill_date ?? now());

        return $this->buildRetentions($bill, 'bill_payment', $payment->id, $periodDate, $paymentRatio);
    }

    public function storePaymentRetentions(BillPayment $payment): void
    {
        $this->persist($this->buildPaymentRetentions($payment));
    }

    protected function persist(Collection $payloads): void
    {
        foreach ($payloads as $payload) {
            RetentionRecord::create($payload);
        }
    }

    protected function buildRetentions(Bill $bill, string $documentType, int $documentId, Carbon $periodDate, float $ratio = 1): Collection
    {
        $retentions = collect();
        $baseAmount = max(0, ($bill->getSubTotal() - $bill->getTotalDiscount()) * $ratio);
        $taxAmount = max(0, $bill->getTotalTax() * $ratio);

        $itbisRate = (float) config('dgii.itbis_retention_rate', 0);
        if ($taxAmount > 0 && $itbisRate > 0) {
            $retentions->push($this->makeRecordPayload($bill, 'itbis', $baseAmount, $taxAmount, $itbisRate, $documentType, $documentId, $periodDate));
        }

        $isrRate = (float) config('dgii.isr_retention_rate', 0);
        if ($baseAmount > 0 && $isrRate > 0) {
            $retentions->push($this->makeRecordPayload($bill, 'isr', $baseAmount, 0, $isrRate, $documentType, $documentId, $periodDate));
        }

        return $retentions;
    }

    protected function makeRecordPayload(
        Bill $bill,
        string $type,
        float $baseAmount,
        float $taxAmount,
        float $rate,
        string $documentType,
        int $documentId,
        Carbon $periodDate
    ): array {
        $retainedAmount = $type === 'itbis' ? $taxAmount * $rate : $baseAmount * $rate;

        return [
            'document_type' => $documentType,
            'document_id' => $documentId,
            'retention_type' => $type,
            'base_amount' => round($baseAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'retained_amount' => round($retainedAmount, 2),
            'rate' => $rate,
            'ncf_number' => $bill->ncf_number,
            'period_month' => (int) $periodDate->format('m'),
            'period_year' => (int) $periodDate->format('Y'),
            'created_by' => $bill->created_by,
        ];
    }
}
