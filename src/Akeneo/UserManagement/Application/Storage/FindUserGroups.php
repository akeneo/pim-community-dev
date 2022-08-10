<?php

namespace Akeneo\UserManagement\Application\Storage;

interface FindUserGroups
{
    public const DEFAULT_LIMIT = 25;

    /** @return array<int, {id: int, name: string}> */
    public function __invoke(
        ?string $search = null,
        ?int $searchAfterId = null,
        int $limit = self::DEFAULT_LIMIT
    ): array;
}
