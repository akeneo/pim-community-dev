<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AppAuthorization
{
    public string $clientId;
    public string $scope;
    public string $redirectUri;
    public ?string $state;

    private function __construct()
    {

    }

    public static function createFromRequest(
        string $clientId,
        string $scope,
        string $redirectUri,
        ?string $state = null
    ): self {
        $self = new self();
        $self->clientId = $clientId;
        $self->scope = $scope;
        $self->redirectUri = $redirectUri;
        $self->state = $state;

        return $self;
    }

    public static function createFromNormalized(array $normalized): self
    {
        $self = new self();
        $self->clientId = $normalized['client_id'];
        $self->scope = $normalized['scope'];
        $self->redirectUri = $normalized['redirect_uri'];
        $self->state = $normalized['state'];

        return $self;
    }

    public function normalize(): array
    {
        return [
            'client_id' => $this->clientId,
            'scope' => $this->scope,
            'redirect_uri' => $this->redirectUri,
            'state' => $this->state,
        ];
    }
}
