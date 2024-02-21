<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\Domain\Model\UserRole as DomainUserRole;
use Akeneo\UserManagement\Domain\Storage\FindAllUserRoles;
use Akeneo\UserManagement\ServiceApi\UserRole\ListUserRoleInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\UserRole;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListUserRoleHandler implements ListUserRoleInterface
{
    public function __construct(
        private FindAllUserRoles $findAllUserRoles,
    ) {
    }

    /**
     * @return UserRole[]
     */
    public function all(): array
    {
        $result = ($this->findAllUserRoles)();

        return array_map(static fn (DomainUserRole $userRole) => new UserRole(
            $userRole->getId(),
            $userRole->getRole(),
            $userRole->getLabel(),
            $userRole->getType(),
        ), $result);
    }
}
