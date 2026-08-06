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
        Schema::create('template_versions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index();
            $table->string('version');
            $table->json('manifest');
            $table->string('zip_path');
            $table->string('checksum');
            $table->foreignId('user_id')->nullable()->nullOnDelete();
            $table->timestamps();

            $table->unique(['slug', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_versions');
    }
};
