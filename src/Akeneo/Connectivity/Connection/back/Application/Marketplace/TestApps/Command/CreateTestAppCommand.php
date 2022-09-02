<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTestAppCommand
{
    public function __construct(
        private string $clientId,
        private string $name,
        private string $activateUrl,
        private string $callbackUrl,
        private int $userId,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getActivateUrl(): string
    {
        return $this->activateUrl;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }
}
