<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Command\UserIntents;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddPermission implements UserIntent
{
    /**
     * @param array<int> $userGroupIds
     */
    public function __construct(
        private readonly string $type,
        private readonly array $userGroupIds,
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return array<int>
     */
    public function userGroupIds(): array
    {
        return $this->userGroupIds;
    }
}
