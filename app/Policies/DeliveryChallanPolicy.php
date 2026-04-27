<?php

namespace App\Policies;

use App\Models\DeliveryChallan;
use App\Models\User;

class DeliveryChallanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager', 'Operator'])->exists();
    }

    public function view(User $user, DeliveryChallan $deliveryChallan): bool
    {
        return $user->tenant_id === $deliveryChallan->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager'])->exists();
    }

    public function update(User $user, DeliveryChallan $deliveryChallan): bool
    {
        return $this->view($user, $deliveryChallan) && $this->create($user);
    }

    public function delete(User $user, DeliveryChallan $deliveryChallan): bool
    {
        return $this->update($user, $deliveryChallan);
    }
}
