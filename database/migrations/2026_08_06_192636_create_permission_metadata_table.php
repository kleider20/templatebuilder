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
        Schema::create('permission_metadata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_id')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('category')->default('General');
            $table->boolean('is_critical')->default(false);
            $table->timestamps();

            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_metadata');
    }
};
