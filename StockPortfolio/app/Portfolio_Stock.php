<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portfolio_Stock extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ticker_symbol', 'share_count', 'purchase_date', 'purchase_price'];

    /**
     * Gets the user that owns the portfolio
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function portfolios()
    {
        return $this->belongsTo('App\Portfolio');
    }
}
