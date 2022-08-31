<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\UserManagement\Domain\Storage;

use Akeneo\UserManagement\Domain\Model\User;

interface FindUsers
{
    public const DEFAULT_LIMIT = 25;

    /**
     * @param int[]|null $includeIds
     * @param int[]|null $includeGroupIds
     *
     * @return User[]
     */
    public function __invoke(
        ?string $search = null,
        ?int $searchAfterId = null,
        ?array $includeIds = null,
        ?array $includeGroupIds = null,
        int $limit = self::DEFAULT_LIMIT,
    ): array;
}
