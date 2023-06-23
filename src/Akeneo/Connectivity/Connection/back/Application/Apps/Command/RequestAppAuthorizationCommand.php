<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAppAuthorizationCommand
{
    public function __construct(
        private string $clientId,
        private string $responseType,
        private mixed $scope,
        private string $callbackUrl,
        private ?string $state = null
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getResponseType(): string
    {
        return $this->responseType;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function getState(): ?string
    {
        return $this->state;
    }
}
