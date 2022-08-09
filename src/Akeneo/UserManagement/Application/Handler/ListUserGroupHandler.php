<?php

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\API\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\API\UserGroup\UserGroup;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;

class ListUserGroupHandler
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository
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
        return array_map(static function (GroupInterface $group) {
            return new UserGroup(
                $group->getId(),
                $group->getName(),
            );
        }, $this->groupRepository->findBy(['type' => Group::TYPE_DEFAULT]));
    }

}
