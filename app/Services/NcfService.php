<?php

namespace App\Services;

use App\Models\NcfSequence;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NcfService
{
    /**
     * Reserve the next available NCF number for the given sequence respecting validity rules.
     */
    public function reserveNextNumber(NcfSequence $sequence, Carbon $issueDate): string
    {
        return DB::transaction(function () use ($sequence, $issueDate) {
            $lockedSequence = NcfSequence::lockForUpdate()->findOrFail($sequence->id);

            $this->assertCanUseSequence($lockedSequence, $issueDate);

            $nextNumber = $lockedSequence->nextCandidate();
            if ($nextNumber > $lockedSequence->end_number) {
                throw ValidationException::withMessages([
                    'ncf_sequence_id' => __('The NCF sequence is depleted.'),
                ]);
            }

            $lockedSequence->current_number = $nextNumber;
            $lockedSequence->save();

            return $lockedSequence->formatNcf($nextNumber);
        });
    }

    /**
     * Validate that the sequence is active, within dates and connected NCF type is enabled.
     */
    public function assertCanUseSequence(NcfSequence $sequence, Carbon $issueDate): void
    {
        if (!$sequence->is_active) {
            throw ValidationException::withMessages([
                'ncf_sequence_id' => __('The selected NCF series is inactive.'),
            ]);
        }

        if ($sequence->ncfType && !$sequence->ncfType->is_active) {
            throw ValidationException::withMessages([
                'ncf_sequence_id' => __('The selected NCF type is inactive.'),
            ]);
        }

        if (!$sequence->isWithinValidity($issueDate)) {
            throw ValidationException::withMessages([
                'ncf_sequence_id' => __('The selected NCF series is outside its validity period.'),
            ]);
        }
    }
}
