<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\ServiceApi\UserRole;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class UserRole
{
    public function __construct(
        private int $id,
        private string $role,
        private string $label,
        private string $type,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
