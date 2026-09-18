<?php

namespace App\Actions;

use App\Models\Dashboard;
use App\Models\User;
use Illuminate\Support\Str;

class CreateDashboardForUserAction
{
    public function handle(User $user): Dashboard
    {
        return $user->dashboard()->firstOrCreate([], [
            'name' => $this->defaultName($user),
            'slug' => $this->defaultSlug($user),
            'shell_key' => Dashboard::generateShellKey(),
            'plan' => Dashboard::PLAN_STARTER,
            'status' => Dashboard::STATUS_ACTIVE,
        ]);
    }

    private function defaultName(User $user): string
    {
        return $user->name.' dashboard';
    }

    private function defaultSlug(User $user): string
    {
        return (Str::slug($user->name) ?: 'dashboard').'-'.$user->id;
    }
}