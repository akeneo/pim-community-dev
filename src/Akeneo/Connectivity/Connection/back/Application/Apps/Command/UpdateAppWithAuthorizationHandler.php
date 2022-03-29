<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\UpdateConnectedAppInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppQueryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateAppWithAuthorizationHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private AppAuthorizationSessionInterface $appAuthorizationSession,
        private UpdateConnectedAppQueryInterface $updateConnectedAppQuery,
    ) {
    }

    public function handle(UpdateAppWithAuthorizationCommand $command): void
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

        $this->updateConnectedAppQuery->execute(
            $appAuthorization->getAuthorizationScopes()->getScopes(),
            $appId,
        );
    }
}
