<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthenticationUser;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAsymmetricKeysQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\OpenIdScopeMapper;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateJsonWebToken
{
    private Key $privateKey;
    private Key $publicKey;

    private Clock $clock;
    private PimUrl $pimUrl;

    public function __construct(
        Clock $clock,
        PimUrl $pimUrl,
        GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery
    ) {
        ['public_key' => $publicKey, 'private_key' => $privateKey] = $getAsymmetricKeysQuery->execute();
        $this->privateKey = InMemory::plainText($privateKey);
        $this->publicKey = InMemory::plainText($publicKey);

        $this->clock = $clock;
        $this->pimUrl = $pimUrl;
    }

    public function create(string $clientId, AppAuthenticationUser $appAuthenticationUser): string
    {
        $jwtConfig = Configuration::forAsymmetricSigner(
            new Sha256(),
            $this->privateKey,
            $this->publicKey
        );

        $uuid = Uuid::uuid4()->toString();
        $now = $this->clock->now();

        $jwtTokenBuilder = $jwtConfig->builder()
            ->issuedBy($this->pimUrl->getPimUrl())
            ->identifiedBy($uuid)
            ->relatedTo($appAuthenticationUser->getAppUserId())
            ->permittedFor($clientId)
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'));

        $consentedAuthenticationScopes = $appAuthenticationUser->getConsentedAuthenticationScopes();
        if (false === $consentedAuthenticationScopes->hasScope(OpenIdScopeMapper::SCOPE_OPENID)) {
            throw new \LogicException('OpenID must be consented to create a JWT');
        }
        if ($consentedAuthenticationScopes->hasScope(OpenIdScopeMapper::SCOPE_PROFILE)) {
            $jwtTokenBuilder
                ->withClaim('firstname', $appAuthenticationUser->getFirstname())
                ->withClaim('lastname', $appAuthenticationUser->getLastname());
        }
        if ($consentedAuthenticationScopes->hasScope(OpenIdScopeMapper::SCOPE_EMAIL)) {
            $jwtTokenBuilder
                ->withClaim('email', $appAuthenticationUser->getEmail());
        }

        $jwtToken = $jwtTokenBuilder->getToken(
            $jwtConfig->signer(),
            $jwtConfig->signingKey()
        );

        return $jwtToken->toString();
    }
}
