<?php

namespace Akeneo\Platform\JobAutomation\Domain\Query;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;

interface FindUsersByUserGroupIdQueryInterface
{
    /**
     * @param int[] $userGroupIds
     *
     * @return UserToNotify[]
     */
    public function execute(array $userGroupIds): array;
}
