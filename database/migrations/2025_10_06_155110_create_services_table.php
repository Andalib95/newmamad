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
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_category_id')->index('services_service_category_id_foreign');
            $table->string('title', 191);
            $table->string('alias', 191)->unique();
            $table->text('keywords')->nullable();
            $table->text('description')->nullable();
            $table->text('intro_text')->nullable();
            $table->longText('body')->nullable();
            $table->boolean('publish')->default(false);
            $table->boolean('index')->default(true);
            $table->boolean('follow')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
