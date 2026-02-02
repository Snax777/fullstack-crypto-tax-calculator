<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  use HasFactory;

  protected $table = 'transactions';

  protected $fillable = [
    'date',
    'type',
    'coin',
    'quantity',
    'price_zar',
    'fee',
    'session_id'
  ];

  protected $casts = [
    'date' => 'date',
    'quantity' => 'decimal:18',
    'price_zar' => 'decimal:2',
    'fee' => 'decimal:2',
  ];

  public static function rules(): array
  {
    return [
      'date' => 'required|date',
      'type' => 'required|in:BUY,SELL,TRADE',
      'coin' => 'required|string|max:10',
      'quantity' => 'required|numeric|min:0',
      'price_zar' => 'required|numeric|min:0',
      'fee' => 'nullable|numeric|min:0',
      'session_id' => 'nullable|string',
    ];
  }

  public function scopeForSession($query, $sessionId)
  {
    return $query->where('session_id', $sessionId);
  }

  public function scopeOrderedByDate($query)
  {
    return $query->orderBy('date', 'asc')->orderBy('created_at', 'asc');
  }

  public function scopeForCoin($query, $coin)
  {
    return $query->where('coin', $coin);
  }
}
