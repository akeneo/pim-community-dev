<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\UserGroup\FindUserGroupsForSecurityIdentifierInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindUserGroupsForSecurityIdentifier implements FindUserGroupsForSecurityIdentifierInterface
{
    /** @var array */
    private $groupsForUsers = [];

    /**
     * @return UserGroupIdentifier[]
     */
    public function __invoke(SecurityIdentifier $securityIdentifier): array
    {
        return $this->groupsForUsers[$securityIdentifier->stringValue()] ?? [];
    }

    public function stubWith(array $groupsForUsers): void
    {
        $this->groupsForUsers = $groupsForUsers;
    }
}
