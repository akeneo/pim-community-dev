<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionWebhook
{
    public function __construct(
        private string $connectionCode,
        private bool $enabled,
        private ?string $secret = null,
        private ?string $url = null,
        private bool $isUsingUuid = false,
    ) {
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function secret(): ?string
    {
        return $this->secret;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function isUsingUuid(): bool
    {
        return $this->isUsingUuid;
    }

    /**
     * @return array{
     *  connectionCode: string,
     *  enabled: boolean,
     *  secret: ?string,
     *  url: ?string,
     *  isUsingUuid: boolean,
     * }
     */
    public function normalize(): array
    {
        return [
            'connectionCode' => $this->connectionCode(),
            'enabled' => $this->enabled(),
            'secret' => $this->secret(),
            'url' => $this->url(),
            'isUsingUuid' => $this->isUsingUuid(),
        ];
    }
}
