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
        Schema::create('role_metadata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('color')->default('#6B7280');
            $table->string('icon_color')->default('#FFFFFF');
            $table->string('icon')->default('user');
            $table->boolean('show_in_register')->default(false);
            $table->boolean('is_protected')->default(false);
            $table->boolean('can_edit_permissions')->default(true);
            $table->boolean('is_fallback')->default(false);
            $table->string('level')->default('Básico');
            $table->unsignedBigInteger('users_count')->default(0);
            $table->unsignedBigInteger('permissions_count')->default(0);
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_metadata');
    }
};
