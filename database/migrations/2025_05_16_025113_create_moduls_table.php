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
        Schema::create('moduls', function (Blueprint $table) {
            $table->id();
            $table->date('input_date');
            $table->string('nip');
            $table->string('height');
            $table->string('project_name');
            $table->string('product_name');
            $table->string('code_cabinet');
            $table->string('description_unit');
            $table->string('box_carcase_shape');
            $table->string('finishing');
            $table->string('layer_position');
            $table->string('closing_system');
            $table->string('number_of_closures');
            $table->string('type_of_closure');
            $table->string('handle');
            $table->string('acc');
            $table->string('lamp');
            $table->string('plint');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moduls');
    }
};
