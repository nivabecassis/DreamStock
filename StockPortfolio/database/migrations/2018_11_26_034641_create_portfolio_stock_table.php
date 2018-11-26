<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortfolioStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portfolio_stock', function (Blueprint $table) {
            $table->string('ticker_symbol');
            $table->integer('portfolio_id')->unsigned()->index();
            $table->integer('share_count')->unsigned();
            $table->timestamp('purchase_date');
            $table->decimal('purchase_price', 10, 2);
            $table->foreign('portfolio_id')->references('id')->on('portfolio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portfolio_stock');
    }
}
