<?php

namespace Akeneo\UserManagement\back\Application\Storage;

use Akeneo\UserManagement\back\Domain\Model\Group;

interface FindUserGroups
{
    public const DEFAULT_LIMIT = 25;

    /** @return Group[] */
    public function __invoke(
        ?string $search = null,
        ?int $searchAfterId = null,
        int $limit = self::DEFAULT_LIMIT
    ): array;
}
