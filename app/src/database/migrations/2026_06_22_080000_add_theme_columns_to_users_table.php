<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('main_theme')->default('neutral')->after('remember_token');
            $table->string('secondary_theme')->default('slate')->after('main_theme');
            $table->string('accent_theme')->default('sky')->after('secondary_theme');
            $table->string('info_theme')->default('blue')->after('accent_theme');
            $table->string('success_theme')->default('green')->after('info_theme');
            $table->string('warning_theme')->default('yellow')->after('success_theme');
            $table->string('error_theme')->default('red')->after('warning_theme');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'main_theme', 'secondary_theme', 'accent_theme',
                'info_theme', 'success_theme', 'warning_theme', 'error_theme',
            ]);
        });
    }
};
