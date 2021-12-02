<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAppAuthorizationCommand
{
    private string $clientId;
    private string $responseType;
    private string $scope;
    private ?string $state;

    public function __construct(
        string $clientId,
        string $responseType,
        string $scope,
        ?string $state = null
    ) {
        $this->clientId = $clientId;
        $this->responseType = $responseType;
        $this->scope = $scope;
        $this->state = $state;
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

    public function getState(): ?string
    {
        return $this->state;
    }
}
