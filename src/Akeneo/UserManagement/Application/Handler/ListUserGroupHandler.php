<?php

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\API\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\API\UserGroup\UserGroup;
use Akeneo\UserManagement\Application\Storage\FindUserGroups;

class ListUserGroupHandler
{
    public function __construct(
        private FindUserGroups $findUserGroups
    ) {
    }

    /**
     * @param ListUserGroupQuery $query
     * @return UserGroup[]
     */
    public function __invoke(ListUserGroupQuery $query): array
    {
        // @todo replace with SQL native query
        // @todo implement optional arguments in $query to allow research and pagination
        // @todo exclude the default user group

        return array_map(static function (array $group) {
            return new UserGroup(
                $group['id'],
                $group['name'],
            );
        }, ($this->findUserGroups)(
            $query->getSearchName(),
            $query->getSearchAfterId(),
            $query->getLimit(),
        ));
    }

}
