<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeMapperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAppAuthorizationHandler
{
    private ValidatorInterface $validator;
    private AppAuthorizationSessionInterface $session;
    private ScopeMapperInterface $scopeMapper;

    public function __construct(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
        ScopeMapperInterface $scopeMapper
    ) {
        $this->validator = $validator;
        $this->session = $session;
        $this->scopeMapper = $scopeMapper;
    }

    public function handle(RequestAppAuthorizationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (\count($violations) > 0) {
            throw new InvalidAppAuthorizationRequest($violations);
        }

        $supportedScopes = $this->scopeMapper->getAllScopes();
        $requestedScopes = \explode(' ', $command->getScope());
        $allowedScopes = \array_intersect($requestedScopes, $supportedScopes);

        $authorization = AppAuthorization::createFromRequest(
            $command->getClientId(),
            \implode(' ', $allowedScopes),
            $command->getRedirectUri(),
            $command->getState(),
        );

        $this->session->initialize($authorization);
    }
}
