<?php

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupHandlerInterface;
use Akeneo\UserManagement\Domain\Model\Group as DomainGroup;
use Akeneo\UserManagement\Domain\Storage\FindUserGroups;
use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroup;

class ListUserGroupHandler implements ListUserGroupHandlerInterface
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
