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
        Schema::create('part_components', function (Blueprint $table) {
            $table->id();
            $table->string('cat');
            $table->string('code');
            $table->string('name');
            $table->string('val');
            $table->string('KS');
            $table->string('Ket')->nullable();
            $table->string('number_of_sub')->nullable();
            $table->string('material')->nullable();
            $table->string('thickness')->nullable();
            $table->string('minifix')->nullable();
            $table->string('dowel')->nullable();
            $table->string('elbow_type')->nullable();
            $table->string('screw_type')->nullable();
            $table->string('V')->nullable();
            $table->string('V2')->nullable();
            $table->string('H')->nullable();
            $table->string('profile3')->nullable();
            $table->string('profile2')->nullable();
            $table->string('profile')->nullable();
            $table->string('outside')->nullable();
            $table->string('inside')->nullable();
            $table->string('P1')->nullable();
            $table->string('P2')->nullable();
            $table->string('L1')->nullable();
            $table->string('L2')->nullable();
            $table->string('rail')->nullable();
            $table->string('hinge')->nullable();
            $table->string('anodize')->nullable();
            $table->string('number_of_anodize')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_components');
    }
};
