<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves the per-user colour settings to the fltc theme model: a skin (the look),
 * a theme (the colour scheme) and, for the `custom` theme, one palette per role.
 *
 * Users who had changed any of the old palette columns get the `custom` theme with
 * their palettes mapped onto the new roles; everyone else falls back to the defaults.
 */
return new class extends Migration
{
    /** Old column => new role. The old accent coloured interactive elements, which is `primary` now. */
    private const ROLE_MAP = [
        'main_theme' => 'base',
        'secondary_theme' => 'secondary',
        'accent_theme' => 'primary',
        'info_theme' => 'info',
        'success_theme' => 'success',
        'warning_theme' => 'warning',
        'error_theme' => 'danger',
    ];

    private const OLD_DEFAULTS = [
        'main_theme' => 'neutral',
        'secondary_theme' => 'slate',
        'accent_theme' => 'sky',
        'info_theme' => 'blue',
        'success_theme' => 'green',
        'warning_theme' => 'yellow',
        'error_theme' => 'red',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('skin')->nullable()->after('remember_token');
            $table->string('theme')->nullable()->after('skin');
            $table->json('theme_colors')->nullable()->after('theme');
        });

        DB::table('users')->orderBy('id')->each(function (object $user) {
            $old = array_intersect_key((array) $user, self::OLD_DEFAULTS);

            if ($old == array_intersect_key(self::OLD_DEFAULTS, $old)) {
                return;
            }

            $colors = [];
            foreach (self::ROLE_MAP as $column => $role) {
                $colors[$role] = $old[$column] ?? self::OLD_DEFAULTS[$column];
            }
            $colors['accent'] = $colors['primary'];

            DB::table('users')->where('id', $user->id)->update([
                'theme' => 'custom',
                'theme_colors' => json_encode($colors),
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_keys(self::ROLE_MAP));
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (self::OLD_DEFAULTS as $column => $default) {
                $table->string($column)->default($default)->after('remember_token');
            }
        });

        DB::table('users')->where('theme', 'custom')->orderBy('id')->each(function (object $user) {
            $colors = json_decode($user->theme_colors ?? '{}', true) ?: [];
            $update = [];
            foreach (self::ROLE_MAP as $column => $role) {
                if (isset($colors[$role])) {
                    $update[$column] = $colors[$role];
                }
            }

            DB::table('users')->where('id', $user->id)->update($update);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['skin', 'theme', 'theme_colors']);
        });
    }
};
