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
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('article_id')->index('comments_article_id_foreign');
            $table->string('author_name', 191)->nullable();
            $table->string('author_email', 191)->nullable();
            $table->string('author_ip', 191)->nullable();
            $table->text('author_agent')->nullable();
            $table->text('body')->nullable();
            $table->text('answer')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
