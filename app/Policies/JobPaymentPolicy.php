<?php

namespace App\Policies;

use App\Models\JobPayment;
use App\Models\User;

class JobPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager'])->exists();
    }

    public function view(User $user, JobPayment $jobPayment): bool
    {
        return $user->tenant_id === $jobPayment->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, JobPayment $jobPayment): bool
    {
        return $this->view($user, $jobPayment) && $this->viewAny($user);
    }

    public function delete(User $user, JobPayment $jobPayment): bool
    {
        return $this->update($user, $jobPayment);
    }
}
