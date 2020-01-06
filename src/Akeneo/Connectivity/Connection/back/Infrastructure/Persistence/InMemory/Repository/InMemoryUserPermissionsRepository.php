<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;

final class InMemoryUserPermissionsRepository
{
    private $permissions = [];

    public function add(User $user, string $role, string $group)
    {
        $this->permissions[$user->id()] = [
            'user' => $user,
            'role' => [
                'id' => 1,
                'name' => $role
            ],
            'group' => [
                'id' => 2,
                'name' => $group
            ]
        ];
    }

    public function getByUserId(int $id)
    {
        return $this->permissions[$id];
    }
}
