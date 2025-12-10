<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetentionRecord extends Model
{
    protected $fillable = [
        'document_type',
        'document_id',
        'retention_type',
        'base_amount',
        'tax_amount',
        'retained_amount',
        'rate',
        'ncf_number',
        'period_month',
        'period_year',
        'created_by',
    ];
}
