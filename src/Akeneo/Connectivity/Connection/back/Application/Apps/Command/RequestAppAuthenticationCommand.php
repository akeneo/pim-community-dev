<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAppAuthenticationCommand
{
    public function __construct(
        private string $appId,
        private int $pimUserId,
        private ScopeList $requestedAuthenticationScopes
    ) {
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getPimUserId(): int
    {
        return $this->pimUserId;
    }

    public function getRequestedAuthenticationScopes(): ScopeList
    {
        return $this->requestedAuthenticationScopes;
    }
}
