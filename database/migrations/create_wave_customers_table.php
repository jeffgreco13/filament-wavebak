<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wave_customers', function (Blueprint $table) {
            // $table->id();
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('currency')->nullable();
            $table->json('address')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->json('meta')->nullable(); // can store outstanding balance and overdue balance

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(
            'wave_customers'
        );
    }
};
