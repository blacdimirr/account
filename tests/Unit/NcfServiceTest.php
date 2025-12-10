<?php

namespace Tests\Unit;

use App\Models\NcfSequence;
use App\Models\NcfType;
use App\Services\NcfService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class NcfServiceTest extends TestCase
{
    public function test_it_builds_next_ncf_number_and_validates_dates(): void
    {
        $type = new NcfType(['code' => 'B01', 'is_active' => true]);
        $sequence = new NcfSequence([
            'ncf_type_id' => 1,
            'serie' => 'B01',
            'start_number' => 1,
            'end_number' => 5,
            'current_number' => 1,
            'valid_from' => Carbon::now()->subDay(),
            'valid_until' => Carbon::now()->addDay(),
            'is_active' => true,
        ]);
        $sequence->setRelation('ncfType', $type);

        $service = new NcfService();

        // Validation should pass for dates inside the window
        $service->assertCanUseSequence($sequence, Carbon::now());
        $this->assertSame(2, $sequence->nextCandidate());
        $this->assertSame('B0100000002', $sequence->formatNcf(2));
    }

    public function test_it_rejects_inactive_sequences(): void
    {
        $sequence = new NcfSequence([
            'start_number' => 1,
            'end_number' => 1,
            'valid_from' => Carbon::now()->subDay(),
            'valid_until' => Carbon::now()->addDay(),
            'is_active' => false,
        ]);

        $service = new NcfService();

        $this->expectException(ValidationException::class);
        $service->assertCanUseSequence($sequence, Carbon::now());
    }
}
