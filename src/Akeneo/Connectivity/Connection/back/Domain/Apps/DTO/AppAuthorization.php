<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppAuthorization
{
    public string $clientId;

    private ScopeList $authorizationScope;
    private ScopeList $authenticationScope;
    private string $redirectUri;
    private ?string $state = null;

    private function __construct()
    {
    }

    public static function createFromRequest(
        string $clientId,
        ScopeList $authorizationScope,
        ScopeList $authenticationScope,
        string $redirectUri,
        ?string $state = null
    ): self {
        $self = new self();
        $self->clientId = $clientId;
        $self->authorizationScope = $authorizationScope;
        $self->authenticationScope = $authenticationScope;
        $self->redirectUri = $redirectUri;
        $self->state = $state;

        return $self;
    }

    /**
     * @param array{
     *     client_id: string,
     *     authorization_scope: string,
     *     authentication_scope: string,
     *     redirect_uri: string,
     *     state: string|null,
     * } $normalized
     */
    public static function createFromNormalized(array $normalized): self
    {
        $self = new self();
        $self->clientId = $normalized['client_id'];
        $self->authorizationScope = ScopeList::fromScopeString($normalized['authorization_scope']);
        $self->authenticationScope = ScopeList::fromScopeString($normalized['authentication_scope']);
        $self->redirectUri = $normalized['redirect_uri'];
        $self->state = $normalized['state'];

        return $self;
    }

    /**
     * @return array{
     *     client_id: string,
     *     authorization_scope: string,
     *     authentication_scope: string,
     *     redirect_uri: string,
     *     state: string|null,
     * }
     */
    public function normalize(): array
    {
        return [
            'client_id' => $this->clientId,
            'authorization_scope' => $this->authorizationScope->toScopeString(),
            'authentication_scope' => $this->authenticationScope->toScopeString(),
            'redirect_uri' => $this->redirectUri,
            'state' => $this->state,
        ];
    }

    public function getAllScopes(): ScopeList
    {
        return $this->authorizationScope->addScopes($this->authenticationScope);
    }

    public function getAuthorizationScopes(): ScopeList
    {
        return $this->authorizationScope;
    }

    public function getAuthenticationScopes(): ScopeList
    {
        return $this->authenticationScope;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }
}
