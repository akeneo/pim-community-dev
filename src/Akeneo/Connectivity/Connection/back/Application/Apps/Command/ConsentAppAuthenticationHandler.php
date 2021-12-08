<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthenticationUserProviderInterface;
use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\ConnectedPimUserProviderInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Clock;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConsentAppAuthenticationHandler
{
    private GetAppConfirmationQueryInterface $getAppConfirmationQuery;
    private AppAuthorizationSessionInterface $appAuthorizationSession;
    private AppAuthenticationUserProviderInterface $appAuthenticationUserProvider;
    private CreateUserConsentQueryInterface $createUserConsentQuery;
    private Clock $clock;
    private ConnectedPimUserProviderInterface $connectedPimUserProvider;

    public function __construct(
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        AppAuthenticationUserProviderInterface $appAuthenticationUserProvider,
        CreateUserConsentQueryInterface $createUserConsentQuery,
        Clock $clock,
        ConnectedPimUserProviderInterface $connectedPimUserProvider
    ) {
        $this->getAppConfirmationQuery = $getAppConfirmationQuery;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->appAuthenticationUserProvider = $appAuthenticationUserProvider;
        $this->createUserConsentQuery = $createUserConsentQuery;
        $this->clock = $clock;
        $this->connectedPimUserProvider = $connectedPimUserProvider;
    }

    public function handle(ConsentAppAuthenticationCommand $command): void
    {
        // @TODO validate command

        $appId = $command->getClientId();

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($appId);
        if (null === $appAuthorization) {
            throw new \LogicException('There is no active app authorization in session');
        }

        if (false === $appAuthorization->getAuthenticationScopes()->hasScope(AuthenticationScope::SCOPE_OPENID)) {
            throw new \LogicException('The app authorization should request the openid scope');
        }

        $appConfirmation = $this->getAppConfirmationQuery->execute($appId);
        if (null === $appConfirmation) {
            throw new \LogicException('The connected app should have been created');
        }

        $appAuthenticationUser = $this->appAuthenticationUserProvider->getAppAuthenticationUser(
            $appConfirmation->getAppId(),
            $this->connectedPimUserProvider->getCurrentUserId()
        );

        $this->createUserConsentQuery->execute(
            $appAuthenticationUser->getPimUserId(),
            $appConfirmation->getAppId(),
            $appAuthorization->getAuthenticationScopes()->getScopes(),
            $this->clock->now()
        );
    }
}
