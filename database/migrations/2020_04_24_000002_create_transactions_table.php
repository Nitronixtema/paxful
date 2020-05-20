<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('coin_id');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('from_wallet_id')->index();
            $table->unsignedBigInteger('to_wallet_id')->index();
            $table->decimal('amount', 18, 8);
            $table->decimal('tax', 18, 8);
            $table->timestamp('created_at');
            $table->foreign('coin_id')->references('id')->on('coins');
            $table->foreign('from_wallet_id')->references('id')->on('wallets');
            $table->foreign('to_wallet_id')->references('id')->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
