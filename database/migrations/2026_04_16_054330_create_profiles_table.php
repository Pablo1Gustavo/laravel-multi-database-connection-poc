<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'secondary';

    public function up(): void
    {
        Schema::connection('secondary')->create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id')->unique();
            $table->string('website')->nullable();
            $table->string('twitter_handle')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('secondary')->dropIfExists('profiles');
    }
};
