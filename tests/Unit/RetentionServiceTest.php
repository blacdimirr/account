<?php

namespace Tests\Unit;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Services\RetentionService;
use Mockery;
use Tests\TestCase;

class RetentionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_builds_itbis_and_isr_retentions()
    {
        $bill = Mockery::mock(Bill::class)->makePartial();
        $bill->id = 10;
        $bill->bill_date = '2024-10-15';
        $bill->ncf_number = 'B0100000001';
        $bill->created_by = 1;
        $bill->has_retention = 1;

        $bill->shouldReceive('getSubTotal')->andReturn(1000);
        $bill->shouldReceive('getTotalDiscount')->andReturn(0);
        $bill->shouldReceive('getTotalTax')->andReturn(180);

        $service = new RetentionService();
        $retentions = $service->buildBillRetentions($bill);

        $this->assertCount(2, $retentions);

        $itbis = $retentions->firstWhere('retention_type', 'itbis');
        $this->assertEquals(54.0, $itbis['retained_amount']);
        $this->assertEquals(180.0, $itbis['tax_amount']);

        $isr = $retentions->firstWhere('retention_type', 'isr');
        $this->assertEquals(50.0, $isr['retained_amount']);
        $this->assertEquals(1000.0, $isr['base_amount']);
    }

    public function test_payment_retention_uses_payment_date_and_ratio()
    {
        $bill = Mockery::mock(Bill::class)->makePartial();
        $bill->id = 20;
        $bill->bill_date = '2024-10-01';
        $bill->ncf_number = 'B0100000009';
        $bill->created_by = 2;
        $bill->has_retention = 1;

        $bill->shouldReceive('getSubTotal')->andReturn(1000);
        $bill->shouldReceive('getTotalDiscount')->andReturn(0);
        $bill->shouldReceive('getTotalTax')->andReturn(180);
        $bill->shouldReceive('getTotal')->andReturn(1180);

        $payment = new BillPayment();
        $payment->id = 99;
        $payment->amount = 590;
        $payment->date = '2024-11-05';
        $payment->setRelation('bill', $bill);

        $service = new RetentionService();
        $retentions = $service->buildPaymentRetentions($payment);

        $this->assertCount(2, $retentions);

        $itbis = $retentions->firstWhere('retention_type', 'itbis');
        $this->assertSame(11, $itbis['period_month']);
        $this->assertEquals(27.0, $itbis['retained_amount']);
    }
}
