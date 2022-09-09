<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Webhook;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Context
{
    public function __construct(
        private string $username,
        private int $userId,
        private bool $useUuid,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function useUuid(): bool
    {
        return $this->useUuid;
    }
}
