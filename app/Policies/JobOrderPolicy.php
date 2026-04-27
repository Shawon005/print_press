<?php

namespace App\Policies;

use App\Models\JobOrder;
use App\Models\User;

class JobOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager', 'Operator'])->exists();
    }

    public function view(User $user, JobOrder $jobOrder): bool
    {
        return $user->tenant_id === $jobOrder->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager'])->exists();
    }

    public function update(User $user, JobOrder $jobOrder): bool
    {
        return $user->tenant_id === $jobOrder->tenant_id && $user->roles()->whereIn('name', ['Admin', 'Manager'])->exists();
    }

    public function delete(User $user, JobOrder $jobOrder): bool
    {
        return $this->update($user, $jobOrder);
    }
}
