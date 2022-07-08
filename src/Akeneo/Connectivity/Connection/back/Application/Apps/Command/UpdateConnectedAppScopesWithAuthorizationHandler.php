<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\UpdateConnectedAppRoleWithScopesInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppScopesQueryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppScopesWithAuthorizationHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private AppAuthorizationSessionInterface $appAuthorizationSession,
        private UpdateConnectedAppScopesQueryInterface $updateConnectedAppScopesQuery,
        private UpdateConnectedAppRoleWithScopesInterface $updateConnectedAppRoleWithScopes,
    ) {
    }

    public function handle(UpdateConnectedAppScopesWithAuthorizationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (\count($violations) > 0) {
            throw new InvalidAppAuthorizationRequestException($violations);
        }

        $appId = $command->getClientId();

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($appId);

        if (null === $appAuthorization) {
            throw new \LogicException('There is no active app authorization in session');
        }

        $authorizationScopes = $appAuthorization->getAuthorizationScopes()->getScopes();

        $this->updateConnectedAppScopesQuery->execute($authorizationScopes, $appId);

        $this->updateConnectedAppRoleWithScopes->execute($appId, $authorizationScopes);
    }
}
