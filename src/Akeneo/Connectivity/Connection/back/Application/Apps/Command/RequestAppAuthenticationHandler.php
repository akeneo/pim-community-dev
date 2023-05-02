<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAppAuthenticationHandler
{
    public function __construct(private GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery, private CreateUserConsentQueryInterface $createUserConsentQuery, private ClockInterface $clock, private ValidatorInterface $validator)
    {
    }

    public function handle(RequestAppAuthenticationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new \InvalidArgumentException((string)$violations->get(0)->getMessage());
        }

        $userId = $command->getPimUserId();
        $appId = $command->getAppId();

        // If openid scope isn't requested, clear all the user consented scopes & skip the authentication.
        if (!$command->getRequestedAuthenticationScopes()->hasScope(AuthenticationScope::SCOPE_OPENID)) {
            $this->createUserConsentQuery->execute(
                $userId,
                $appId,
                [],
                $this->clock->now()
            );

            return;
        }

        $consentedScopes = $this->getUserConsentedAuthenticationScopesQuery->execute($userId, $appId);
        $requestedScopes = $command->getRequestedAuthenticationScopes()->getScopes();

        $requestedScopesAlreadyConsented = \array_intersect($consentedScopes, $requestedScopes);
        $newScopesRequiringConsent = \array_diff($requestedScopes, $requestedScopesAlreadyConsented);

        // Check & remove previously consented scopes that are not requested anymore.
        if (\count($requestedScopesAlreadyConsented) < \count($consentedScopes)) {
            $this->createUserConsentQuery->execute(
                $userId,
                $appId,
                $requestedScopesAlreadyConsented,
                $this->clock->now()
            );
        }

        // Nothing to do if there is no new scopes to consent.
        if (\count($newScopesRequiringConsent) === 0) {
            return;
        }

        // If there is only one new scope and it's openid, then we automatically give consent.
        if (\count($newScopesRequiringConsent) === 1 && \reset($newScopesRequiringConsent) === AuthenticationScope::SCOPE_OPENID) {
            $this->createUserConsentQuery->execute(
                $userId,
                $appId,
                [AuthenticationScope::SCOPE_OPENID],
                $this->clock->now()
            );

            return;
        }

        // Throws if there is one or more new scopes that need consent.
        throw new UserConsentRequiredException(
            $command->getAppId(),
            $command->getPimUserId(),
        );
    }
}
