<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $user->can('manage-users') || $user->teams()->whereKey($team->id)->exists();
    }
}
