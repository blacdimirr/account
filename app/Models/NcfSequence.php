<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class NcfSequence extends Model
{
    protected $fillable = [
        'ncf_type_id',
        'serie',
        'start_number',
        'end_number',
        'current_number',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function ncfType()
    {
        return $this->belongsTo(NcfType::class);
    }

    /**
     * Builds the printable NCF string using the series prefix and zero-padded number.
     */
    public function formatNcf(int $number): string
    {
        $suffix = str_pad((string) $number, 8, '0', STR_PAD_LEFT);

        return ($this->serie ?: '') . $suffix;
    }

    public function nextCandidate(): int
    {
        $base = $this->current_number ?? ($this->start_number - 1);

        return $base + 1;
    }

    public function isWithinValidity(Carbon $issueDate): bool
    {
        $afterStart = empty($this->valid_from) || $issueDate->greaterThanOrEqualTo(Carbon::parse($this->valid_from));
        $beforeEnd = empty($this->valid_until) || $issueDate->lessThanOrEqualTo(Carbon::parse($this->valid_until));

        return $afterStart && $beforeEnd;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        $typeCode = optional($this->ncfType)->code ?: 'NCF';
        $serie = $this->serie ? $this->serie . ' ' : '';
        $period = [];

        if (!empty($this->valid_from)) {
            $period[] = Carbon::parse($this->valid_from)->format('d/m/Y');
        }

        if (!empty($this->valid_until)) {
            $period[] = Carbon::parse($this->valid_until)->format('d/m/Y');
        }

        $periodLabel = count($period) ? '(' . implode(' - ', $period) . ')' : '';

        return trim($typeCode . ' ' . $serie . $periodLabel . ' [' . $this->start_number . '-' . $this->end_number . ']');
    }
}
