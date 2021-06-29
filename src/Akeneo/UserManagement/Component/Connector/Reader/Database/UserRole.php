<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Reader\Database;

use Akeneo\Tool\Component\Connector\Reader\Database\AbstractReader;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRole extends AbstractReader
{
    private ObjectRepository $userRoleRepository;

    public function __construct(ObjectRepository $userRoleRepository)
    {
        $this->userRoleRepository = $userRoleRepository;
    }

    protected function getResults(): \ArrayIterator
    {
        return new \ArrayIterator(
            \array_filter(
                $this->userRoleRepository->findAll(),
                fn (RoleInterface $role): bool => User::ROLE_ANONYMOUS !== $role->getRole()
            )
        );
    }
}
