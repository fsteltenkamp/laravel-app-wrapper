<?php

use App\Livewire\Admin\Permissions\Edit as PermissionsEdit;
use App\Livewire\Admin\Permissions\Index as PermissionsIndex;
use App\Livewire\Admin\Roles\Edit as RolesEdit;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Teams\Edit as TeamsEdit;
use App\Livewire\Admin\Teams\Index as TeamsIndex;
use App\Livewire\Admin\Users\Edit as UsersEdit;
use App\Livewire\Admin\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::livewire('users', UsersIndex::class)->middleware('can:manage-users')->name('admin.users.index');
    Route::livewire('users/{user}/edit', UsersEdit::class)->middleware('can:user.edit')->name('admin.users.edit');

    Route::livewire('roles', RolesIndex::class)->middleware('can:manage-users')->name('admin.roles.index');
    Route::livewire('roles/{role}/edit', RolesEdit::class)->middleware('can:manage-users')->name('admin.roles.edit');

    Route::livewire('permissions', PermissionsIndex::class)->middleware('can:manage-users')->name('admin.permissions.index');
    Route::livewire('permissions/{permission}/edit', PermissionsEdit::class)->middleware('can:manage-users')->name('admin.permissions.edit');

    Route::livewire('teams', TeamsIndex::class)->middleware('can:manage-users')->name('admin.teams.index');
    Route::livewire('teams/{team}/edit', TeamsEdit::class)->middleware('can:manage-users')->name('admin.teams.edit');
});
