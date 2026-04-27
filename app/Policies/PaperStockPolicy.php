<?php

namespace App\Policies;

use App\Models\PaperStock;
use App\Models\User;

class PaperStockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager', 'Operator'])->exists();
    }

    public function view(User $user, PaperStock $paperStock): bool
    {
        return $user->tenant_id === $paperStock->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['Admin', 'Manager'])->exists();
    }

    public function update(User $user, PaperStock $paperStock): bool
    {
        return $this->view($user, $paperStock) && $this->create($user);
    }

    public function delete(User $user, PaperStock $paperStock): bool
    {
        return $this->update($user, $paperStock);
    }
}
