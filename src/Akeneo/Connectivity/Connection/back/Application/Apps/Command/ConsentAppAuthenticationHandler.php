<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsentAppAuthenticationHandler
{
    public function __construct(private GetAppConfirmationQueryInterface $getAppConfirmationQuery, private AppAuthorizationSessionInterface $appAuthorizationSession, private CreateUserConsentQueryInterface $createUserConsentQuery, private ClockInterface $clock, private ValidatorInterface $validator)
    {
    }

    public function handle(ConsentAppAuthenticationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new InvalidAppAuthenticationException($violations);
        }

        $appId = $command->getClientId();

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($appId);
        if (null === $appAuthorization) {
            throw new \LogicException('There is no active app authorization in session');
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
