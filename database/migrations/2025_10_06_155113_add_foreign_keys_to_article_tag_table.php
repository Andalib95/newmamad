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
        Schema::table('article_tag', function (Blueprint $table) {
            $table->foreign(['article_id'])->references(['id'])->on('articles')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tag_id'])->references(['id'])->on('tags')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_tag', function (Blueprint $table) {
            $table->dropForeign('article_tag_article_id_foreign');
            $table->dropForeign('article_tag_tag_id_foreign');
        });
    }
};
