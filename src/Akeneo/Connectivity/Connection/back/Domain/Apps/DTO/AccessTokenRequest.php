<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AccessTokenRequest
{
    public function __construct(
        private string $clientId,
        private string $authorizationCode,
        private string $grantType,
        private string $codeIdentifier,
        private string $codeChallenge
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function getCodeIdentifier(): string
    {
        return $this->codeIdentifier;
    }

    public function getCodeChallenge(): string
    {
        return $this->codeChallenge;
    }
}
