<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\OctoCouplingDefenseSystem\UserManagement\PublicApi\Query\GetUserById;

use Akeneo\Connectivity\OctoCouplingDefenseSystem\UserManagement\PublicApi\Query\GetUserById\GetUserByIdQuery;
use Akeneo\Connectivity\OctoCouplingDefenseSystem\UserManagement\PublicApi\Query\GetUserById\User;
use Akeneo\Tool\Component\Messenger\QueryBusTrait;

trait GetUserByIdQueryTrait
{
    use QueryBusTrait;

    private function getUserById(int $id): User
    {
        return $this->queryBus->query(new GetUserByIdQuery($id));
    }
}
