<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NcfType extends Model
{
    protected $fillable = [
        'code',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sequences()
    {
        return $this->hasMany(NcfSequence::class);
    }
}
