<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'primary';

    public function up(): void
    {
        Schema::connection('primary')->create('labelables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('label_id')->index();
            $table->morphs('labelable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('primary')->dropIfExists('labelables');
    }
};
