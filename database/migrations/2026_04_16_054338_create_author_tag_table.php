<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'primary';

    public function up(): void
    {
        Schema::connection('primary')->create('author_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id')->index();
            $table->unsignedBigInteger('tag_id')->index();
            $table->timestamps();

            $table->unique(['author_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('primary')->dropIfExists('author_tag');
    }
};
