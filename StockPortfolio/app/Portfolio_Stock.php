<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portfolio_Stock extends Model
{
    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'portfolio_stocks';

    /**
     * Indicates if the model should be timestamped
     * 
     * @var bool
     */
    public $timestamps = false;

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
