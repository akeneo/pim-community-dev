<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Webhook;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Context
{
    private string $username;
    private int $userId;

    public function __construct(string $username, int $userId)
    {
        $this->username = $username;
        $this->userId = $userId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
