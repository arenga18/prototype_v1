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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('no_contract');
            $table->string('nip');
            $table->string('product_name');
            $table->string('project_name');
            $table->string('coordinator');
            $table->string('recap_coordinator');
            $table->json('project_status');
            $table->json('product_spesification');
            $table->json('material_thickness_spesification');
            $table->json('coating_spesification');
            $table->json('alu_frame_spesification');
            $table->json('hinges_spesification');
            $table->json('rail_spesification');
            $table->json('glass_spesification');
            $table->json('profile_spesification');
            $table->json('size_distance_spesification');
            $table->json('modul_reference');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
