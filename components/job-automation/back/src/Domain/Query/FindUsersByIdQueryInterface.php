<?php

namespace Akeneo\Platform\JobAutomation\Domain\Query;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;

interface FindUsersByIdQueryInterface
{
    /**
     * @param int[] $ids
     *
     * @return UserToNotify[]
     */
    public function execute(array $ids): array;
}
