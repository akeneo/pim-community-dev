<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Clock;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAppAuthenticationHandler
{
    private GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery;
    private CreateUserConsentQueryInterface $createUserConsentQuery;
    private Clock $clock;

    public function __construct(
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        CreateUserConsentQueryInterface $createUserConsentQuery,
        Clock $clock
    ) {
        $this->getUserConsentedAuthenticationScopesQuery = $getUserConsentedAuthenticationScopesQuery;
        $this->createUserConsentQuery = $createUserConsentQuery;
        $this->clock = $clock;
    }

    public function handle(RequestAppAuthenticationCommand $command): void
    {
        // @TODO validate Command & throw InvalidAppAuthenticationRequest()

        if (false === $command->getRequestedAuthenticationScopes()->hasScope(AuthenticationScope::SCOPE_OPENID)) {
            // @TODO do nothing OR throw a MissingOpenidScopeException and ignore the error in authorize?
            return;
        }

        $requestedScopes = $command->getRequestedAuthenticationScopes()->getScopes();
        $alreadyConsentedScopes = $this->getUserConsentedAuthenticationScopesQuery->execute(
            $command->getPimUserId(),
            $command->getAppId()
        );

        $removedScopes = array_diff($alreadyConsentedScopes, $requestedScopes);
        if (count($removedScopes) > 0) {
            $remainingScopes = array_diff($alreadyConsentedScopes, $removedScopes);
            $this->createUserConsentQuery->execute(
                $command->getPimUserId(),
                $command->getAppId(),
                $remainingScopes,
                $this->clock->now()
            );
        }

        $newScopes = array_diff($requestedScopes, $alreadyConsentedScopes);
        if (count($newScopes) > 0) {
            throw new UserConsentRequiredException($command->getAppId(), $command->getPimUserId(), $newScopes);
        }
    }
}
