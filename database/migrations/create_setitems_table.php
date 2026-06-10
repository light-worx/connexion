<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setitems', function ($table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->integer('content_id')->nullable();
            $table->string('content_type')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setitems');
    }
};
