<?php

namespace Akeneo\Connectivity\OctoCouplingDefenseSystem\UserManagement\Implementation\QueryHandler\GetUserById;

use Akeneo\Connectivity\OctoCouplingDefenseSystem\UserManagement\PublicApi\Query\GetUserById\GetUserByIdQuery;
use Akeneo\Connectivity\OctoCouplingDefenseSystem\UserManagement\PublicApi\Query\GetUserById\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserByIdHandler implements MessageSubscriberInterface
{
    private UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public static function getHandledMessages(): iterable
    {
        yield GetUserByIdQuery::class => [
            'bus' => 'query.bus',
        ];
    }

    // TODO: Handle error
    public function __invoke(GetUserByIdQuery $query): User
    {
        /** @var UserInterface $user */
        $user = $this->repository->find($query->getId());

        return new User($user->getId(), $user->getUsername());
    }
}
