<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Model\SecurityIdentifier;
use Akeneo\AssetManager\Domain\Query\UserGroup\FindUserGroupsForSecurityIdentifierInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindUserGroupsForSecurityIdentifier implements FindUserGroupsForSecurityIdentifierInterface
{
    private array $groupsForUsers = [];

    /**
     * @return UserGroupIdentifier[]
     */
    public function find(SecurityIdentifier $securityIdentifier): array
    {
        return $this->groupsForUsers[$securityIdentifier->stringValue()] ?? [];
    }

    public function stubWith(array $groupsForUsers): void
    {
        $this->groupsForUsers = $groupsForUsers;
    }
}
