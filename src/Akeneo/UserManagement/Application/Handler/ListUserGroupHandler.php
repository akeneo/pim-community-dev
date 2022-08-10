<?php

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\API\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\API\UserGroup\UserGroup;
use Akeneo\UserManagement\Application\Storage\FindUserGroups;
use Akeneo\UserManagement\Domain\Model\Group as DomainGroup;

class ListUserGroupHandler
{
    public function __construct(
        private FindUserGroups $findUserGroups
    ) {
    }

    /**
     * @return UserGroup[]
     */
    public function __invoke(ListUserGroupQuery $query): array
    {
        // @todo implement optional arguments in $query to allow research and pagination
        // @todo exclude the default user group
        $result = ($this->findUserGroups)(
            $query->getSearchName(),
            $query->getSearchAfterId(),
            $query->getLimit(),
        );

        return array_map(static function (DomainGroup $group) {
            return new UserGroup(
                $group->getId(),
                $group->getName(),
            );
        }, $result);
    }
}
