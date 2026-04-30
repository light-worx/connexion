<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plan_services', function ($table) {
            $table->id();
            $table->foreignId('service_plan_id')->constrained()->cascadeOnDelete();
            $table->string('time');
            $table->foreignId('person_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['service_plan_id', 'time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_services');
    }
};