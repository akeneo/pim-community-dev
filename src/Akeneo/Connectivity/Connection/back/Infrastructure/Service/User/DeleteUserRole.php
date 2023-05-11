<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Apps\Service\DeleteUserRoleInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteUserRole implements DeleteUserRoleInterface
{
    public function __construct(private RoleRepository $repository, private RemoverInterface $remover)
    {
    }

    public function execute(string $role): void
    {
        $userRole = $this->repository->findOneByIdentifier($role);

        if (null === $userRole) {
            throw new \LogicException(\sprintf('User role "%s" not found.', $role));
        }

        $this->remover->remove($userRole);
    }
}
