<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'portfolios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cash_owned'];

    /**
     * Gets all portfolio stocks for the portfolio
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function portfolio_stocks()
    {
        return $this->hasMany('App\Portfolio_Stock');
    }

    /**
     * Gets the user that owns the portfolio
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo('App\User');
    }
}
