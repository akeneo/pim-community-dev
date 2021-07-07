<?php

namespace Akeneo\UserManagement\Bundle\QueryHandler;

use Akeneo\Tool\Component\Messenger\QueryHandlerInterface;
use Akeneo\UserManagement\ServiceApi\DTO\User;
use Akeneo\UserManagement\ServiceApi\Query\GetUserByIdQuery\GetUserByIdQuery;

final class GetUserByIdHandler implements QueryHandlerInterface
{
    public function __invoke(GetUserByIdQuery $query): User
    {
        return new User($query->getId(), 'julia');
    }
}
