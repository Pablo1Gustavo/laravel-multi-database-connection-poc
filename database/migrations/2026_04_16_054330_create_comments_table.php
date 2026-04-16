<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'secondary';

    public function up(): void
    {
        Schema::connection('secondary')->create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->text('body');
            $table->string('author_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('secondary')->dropIfExists('comments');
    }
};
