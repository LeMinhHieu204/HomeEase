<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('password');
            $table->string('phone', 30)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(true)->after('is_best_seller');
            $table->timestamp('published_at')->nullable()->after('is_published');
            $table->timestamp('archived_at')->nullable()->after('published_at');
            $table->index(['is_published', 'published_at']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('published_at');
            $table->timestamp('archived_at')->nullable()->after('scheduled_at');
            $table->index(['is_published', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'scheduled_at']);
            $table->dropColumn(['scheduled_at', 'archived_at']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropIndex(['is_published', 'published_at']);
            $table->dropColumn(['brand_id', 'is_published', 'published_at', 'archived_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'is_active']);
        });
    }
};
