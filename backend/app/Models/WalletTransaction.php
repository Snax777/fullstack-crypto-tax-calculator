<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'price_at_open',
        'price_at_close',
        'transaction_date'
    ];

    protected $casts = [
        'transaction_date' => 'datetime'
    ]; // Converts 'transaction_date' into `Carbon` instance for (for date manipulation)

    public function wallet() {
        return $this->belongsTo(Wallet::class);
    }

    public function usdZarRates() {
        return 15.85;
    }

    public function getZarValueBaseCostAttribute() {
        if ($this->price_at_open) {
            $usdValue = $this->amount * $this->price_at_open;

            return $usdValue * $this->usdZarRates();
        }

        return 0;
    }

    public function getZarValueEarningAttribute() {
        if ($this->price_at_close) {
            $usdValue = $this->amount * $this->price_at_close;

            return $usdValue * $this->usdZarRates();
        }

        return null;
    }

    public function getZarValueCapitalGainLossAttribute() {
        if ($this->price_at_close) {
            return $this->zar_value_earning - $this->zar_value_base_cost;
        }
    }
}
