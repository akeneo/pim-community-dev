<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAsymmetricKeysQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateJsonWebToken
{
    public function __construct(
        private ClockInterface $clock,
        private PimUrl $pimUrl,
        private GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery
    ) {
    }

    public function create(
        string $clientId,
        string $ppid,
        ScopeList $consentedAuthenticationScopes,
        string $firstName,
        string $lastName,
        string $email
    ): string {
        /**
         * @var non-empty-string $publicKey
         * @var non-empty-string $privateKey
         */
        ['public_key' => $publicKey, 'private_key' => $privateKey] = $this->getAsymmetricKeysQuery->execute(
        )->normalize();
        $privateKey = InMemory::plainText($privateKey);
        $publicKey = InMemory::plainText($publicKey);

        $jwtConfig = Configuration::forAsymmetricSigner(
            new Sha256(),
            $privateKey,
            $publicKey
        );

        $uuid = Uuid::uuid4()->toString();
        $now = $this->clock->now();

        $jwtTokenBuilder = $jwtConfig->builder()
            ->issuedBy($this->pimUrl->getPimUrl())
            ->identifiedBy($uuid)
            ->relatedTo($ppid)
            ->permittedFor($clientId)
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'));

        if (false === $consentedAuthenticationScopes->hasScope(AuthenticationScope::SCOPE_OPENID)) {
            throw new \LogicException('OpenID must be consented to create a JWT');
        }
        if ($consentedAuthenticationScopes->hasScope(AuthenticationScope::SCOPE_PROFILE)) {
            $jwtTokenBuilder = $jwtTokenBuilder
                ->withClaim('firstname', $firstName)
                ->withClaim('lastname', $lastName);
        }
        if ($consentedAuthenticationScopes->hasScope(AuthenticationScope::SCOPE_EMAIL)) {
            $jwtTokenBuilder = $jwtTokenBuilder
                ->withClaim('email', $email);
        }

        $jwtToken = $jwtTokenBuilder->getToken(
            $jwtConfig->signer(),
            $jwtConfig->signingKey()
        );

        return $jwtToken->toString();
    }
}
