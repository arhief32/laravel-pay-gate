<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->char('id_app', 8)->primary('id_app');
            $table->char('pass_app', 40);
            $table->char('transmission_date_time', 14);
            $table->char('bank_id', 3);
            $table->char('terminal_id', 1);
            $table->char('briva_number', 18);
            $table->char('payment_amount', 10);
            $table->char('transaction_id', 9);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
