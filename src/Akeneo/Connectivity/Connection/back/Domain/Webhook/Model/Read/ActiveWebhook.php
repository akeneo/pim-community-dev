<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActiveWebhook
{
    public function __construct(
        private string $connectionCode,
        private int $userId,
        private string $secret,
        private string $url,
        private bool $isUsingUuid,
    ) {
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function isUsingUuid(): bool
    {
        return $this->isUsingUuid;
    }
}
