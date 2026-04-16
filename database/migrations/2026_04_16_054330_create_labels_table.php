<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'secondary';

    public function up(): void
    {
        Schema::connection('secondary')->create('labels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#000000');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('secondary')->dropIfExists('labels');
    }
};
