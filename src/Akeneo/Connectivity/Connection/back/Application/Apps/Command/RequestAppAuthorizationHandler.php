<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAppAuthorizationHandler
{
    private ValidatorInterface $validator;
    private AppAuthorizationSessionInterface $session;

    public function __construct(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session
    ) {
        $this->validator = $validator;
        $this->session = $session;
    }

    public function handle(RequestAppAuthorizationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (count($violations) > 0) {
            throw new InvalidAppAuthorizationRequest($violations);
        }

        $authorization = AppAuthorization::createFromRequest(
            $command->getClientId(),
            $command->getScope(),
            $command->getRedirectUri(),
            $command->getState(),
        );

        $this->session->initialize($authorization);
    }
}
