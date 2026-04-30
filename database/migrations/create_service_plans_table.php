<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_plans', function ($table) {
            $table->id();
            $table->date('date')->unique();
            $table->foreignId('series_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('person_id')->nullable()->constrained('persons')->nullOnDelete();
            $table->string('details')->nullable();
            $table->string('reading')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_plans');
    }
};