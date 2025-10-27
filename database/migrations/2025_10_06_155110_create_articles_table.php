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
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('articles_user_id_foreign');
            $table->unsignedBigInteger('main_category_id')->index('articles_main_category_id_foreign');
            $table->string('title', 191);
            $table->string('alias', 191)->unique();
            $table->text('keywords')->nullable();
            $table->text('description')->nullable();
            $table->text('intro_text')->nullable();
            $table->longText('body')->nullable();
            $table->boolean('publish')->default(false);
            $table->boolean('comment_status')->default(false);
            $table->integer('comment_count')->default(0);
            $table->integer('hits')->default(0);
            $table->boolean('index')->default(true);
            $table->boolean('has_toc')->default(true);
            $table->boolean('follow')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
