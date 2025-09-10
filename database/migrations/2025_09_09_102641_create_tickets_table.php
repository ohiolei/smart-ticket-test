<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
             $table->ulid('id')->primary();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->enum('status', ['open','in_progress','closed'])->default('open');
           $table->enum('category', ['billing','bug','feature', 'account', 'other'])->nullable();         
            $table->text('explanation')->nullable();         
            $table->float('confidence')->nullable();         
            $table->text('note')->nullable();                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
