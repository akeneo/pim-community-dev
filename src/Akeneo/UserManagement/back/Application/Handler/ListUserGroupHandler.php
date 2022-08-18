<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\Domain\Model\Group as DomainGroup;
use Akeneo\UserManagement\Domain\Storage\FindUserGroups;
use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupHandlerInterface;
use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroup;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListUserGroupHandler implements ListUserGroupHandlerInterface
{
    public function __construct(
        private FindUserGroups $findUserGroups,
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

        return array_map(static fn (DomainGroup $group) => new UserGroup(
            $group->getId(),
            $group->getName(),
        ), $result);
    }
}
