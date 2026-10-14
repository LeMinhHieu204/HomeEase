<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('image_url');
            $table->string('link_url')->nullable();
            $table->string('placement')->default('home');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->index(['placement', 'is_active']);
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('alt_text')->nullable();
            $table->string('collection')->default('general');
            $table->string('mime_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['collection', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('banners');
    }
};
