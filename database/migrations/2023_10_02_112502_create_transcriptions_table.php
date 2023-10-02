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
        Schema::create('recording_transcription', function (Blueprint $table) {
            $table->foreignUuid('recording_id')->constrained()->cascadeOnDelete();
            $table->integer('position');
            $table->string('start');
            $table->string('end');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcriptions');
    }
};
