<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * fltc renamed three skins and themes. Stored choices follow, so users keep their look.
 */
return new class extends Migration
{
    private const RENAMED = [
        'cyberpunk' => 'neon',
        'fantasy' => 'parchment',
        'wh40k' => 'gothic',
    ];

    public function up(): void
    {
        $this->rename(self::RENAMED);
    }

    public function down(): void
    {
        $this->rename(array_flip(self::RENAMED));
    }

    /**
     * @param  array<string, string>  $names  old => new
     */
    private function rename(array $names): void
    {
        foreach (['skin', 'theme'] as $column) {
            foreach ($names as $from => $to) {
                DB::table('users')->where($column, $from)->update([$column => $to]);
            }
        }
    }
};
