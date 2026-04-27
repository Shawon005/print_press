<?php

namespace App\Policies;

use App\Models\Ctp;
use App\Models\User;

class CtpPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager', 'Operator'])->exists();
    }

    public function view(User $user, Ctp $ctp): bool
    {
        return $user->tenant_id === $ctp->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager'])->exists();
    }

    public function update(User $user, Ctp $ctp): bool
    {
        return $this->view($user, $ctp) && $this->create($user);
    }

    public function delete(User $user, Ctp $ctp): bool
    {
        return $this->update($user, $ctp);
    }
}
