<?php

namespace Akeneo\UserManagement\Bundle\QueryHandler;

use Akeneo\Tool\Component\Messenger\QueryHandlerInterface;
use Akeneo\UserManagement\Bundle\PublicApi\Query\GetUserById\GetUserByIdQuery;
use Akeneo\UserManagement\Bundle\PublicApi\Query\GetUserById\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserByIdHandler implements QueryHandlerInterface
{
    private UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(GetUserByIdQuery $query): ?User
    {
        /** @var ?UserInterface $user */
        $user = $this->repository->find($query->getId());

        return new User($user->getId(), $user->getUsername());
    }
}
