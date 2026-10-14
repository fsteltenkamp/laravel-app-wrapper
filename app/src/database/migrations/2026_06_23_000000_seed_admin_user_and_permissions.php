<?php

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['name' => 'user.create', 'label' => 'Create users'],
            ['name' => 'user.edit',   'label' => 'Edit users'],
            ['name' => 'user.delete', 'label' => 'Delete users'],
        ];

        foreach ($permissions as $data) {
            Permission::firstOrCreate(['name' => $data['name']], ['label' => $data['label']]);
        }

        $superadmin = Role::firstOrCreate(['name' => 'Superadmin']);
        $superadmin->syncPermissions(['user.create', 'user.edit', 'user.delete']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('Superadmin');
    }

    public function down(): void
    {
        if ($admin = User::where('email', 'admin@example.com')->first()) {
            $admin->delete();
        }

        if ($superadmin = Role::where('name', 'Superadmin')->first()) {
            $superadmin->delete();
        }

        Permission::whereIn('name', ['user.create', 'user.edit', 'user.delete'])->delete();
    }
};
