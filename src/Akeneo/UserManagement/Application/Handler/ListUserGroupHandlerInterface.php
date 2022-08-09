<?php

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\API\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\API\UserGroup\UserGroup;

interface ListUserGroupHandlerInterface
{
    /**
     * @param ListUserGroupQuery $query
     * @return UserGroup[]
     */
    public function __invoke(ListUserGroupQuery $query): array;

}
