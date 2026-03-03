<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Apps\Service\DeleteUserGroupInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteUserGroup implements DeleteUserGroupInterface
{
    public function __construct(private GroupRepository $repository, private RemoverInterface $remover)
    {
    }

    public function execute(string $name): void
    {
        $userGroup = $this->repository->findOneByIdentifier($name);

        if (null === $userGroup) {
            throw new \LogicException(\sprintf('User group with name "%s" not found.', $name));
        }

        $this->remover->remove($userGroup);
    }
}
