<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Api\Command\UserIntents;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddPermission implements UserIntent
{
    /**
     * @param array<array{id: int, label: string}> $userGroups
     */
    public function __construct(
        private readonly string $type,
        private readonly array $userGroups,
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return array<array{id: int, label: string}>
     */
    public function userGroups(): array
    {
        return $this->userGroups;
    }
}
