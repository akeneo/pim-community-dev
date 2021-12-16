<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Clock;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConsentAppAuthenticationHandler
{
    private GetAppConfirmationQueryInterface $getAppConfirmationQuery;
    private AppAuthorizationSessionInterface $appAuthorizationSession;
    private CreateUserConsentQueryInterface $createUserConsentQuery;
    private Clock $clock;
    private ValidatorInterface $validator;

    public function __construct(
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        CreateUserConsentQueryInterface $createUserConsentQuery,
        Clock $clock,
        ValidatorInterface $validator
    ) {
        $this->getAppConfirmationQuery = $getAppConfirmationQuery;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->createUserConsentQuery = $createUserConsentQuery;
        $this->clock = $clock;
        $this->validator = $validator;
    }

    public function handle(ConsentAppAuthenticationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new \InvalidArgumentException($violations->get(0)->getMessage());
        }

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

        $this->createUserConsentQuery->execute(
            $command->getPimUserId(),
            $appConfirmation->getAppId(),
            $appAuthorization->getAuthenticationScopes()->getScopes(),
            $this->clock->now()
        );
    }
}
