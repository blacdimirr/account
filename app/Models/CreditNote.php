<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $fillable = [
        'invoice',
        'customer',
        'amount',
        'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice', 'id');
    }
}
