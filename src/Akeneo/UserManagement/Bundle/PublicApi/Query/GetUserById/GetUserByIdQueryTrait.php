<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\PublicApi\Query\GetUserById;

use Akeneo\Tool\Component\Messenger\QueryBusTrait;

trait GetUserByIdQueryTrait
{
    use QueryBusTrait;

    private function getUserById(int $id): User
    {
        return $this->queryBus->query(new GetUserByIdQuery($id));
    }
}
