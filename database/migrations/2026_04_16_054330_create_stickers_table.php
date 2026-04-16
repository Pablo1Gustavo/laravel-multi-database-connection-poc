<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'secondary';

    public function up(): void
    {
        Schema::connection('secondary')->create('stickers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->morphs('stickerable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('secondary')->dropIfExists('stickers');
    }
};
