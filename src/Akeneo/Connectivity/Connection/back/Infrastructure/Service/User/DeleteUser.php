<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteUser implements DeleteUserInterface
{
    public function __construct(private UserRepositoryInterface $repository, private RemoverInterface $remover)
    {
    }

    public function execute(UserId $userId): void
    {
        $user = $this->repository->find($userId->id());

        if (null === $user) {
            throw new \InvalidArgumentException(\sprintf('User with id "%s" does not exist.', $userId->id()));
        }

        $this->remover->remove($user);
    }
}
