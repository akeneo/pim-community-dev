<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConsentAppAuthenticationCommand
{
    private string $clientId;
    private int $pimUserId;

    public function __construct(string $clientId, int $pimUserId)
    {
        $this->clientId = $clientId;
        $this->pimUserId = $pimUserId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getPimUserId(): int
    {
        return $this->pimUserId;
    }
}
