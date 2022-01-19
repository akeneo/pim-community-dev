<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAppAuthorizationHandler
{
    private ValidatorInterface $validator;
    private AppAuthorizationSessionInterface $session;
    private ScopeMapperRegistry $scopeMapper;
    private GetAppQueryInterface $getAppQuery;

    public function __construct(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
        ScopeMapperRegistry $scopeMapper,
        GetAppQueryInterface $getAppQuery
    ) {
        $this->validator = $validator;
        $this->session = $session;
        $this->scopeMapper = $scopeMapper;
        $this->getAppQuery = $getAppQuery;
    }

    public function handle(RequestAppAuthorizationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (\count($violations) > 0) {
            throw new InvalidAppAuthorizationRequestException($violations);
        }

        $app = $this->getAppQuery->execute($command->getClientId());
        if (null === $app) {
            throw new \ErrorException('App should exists when validating the authorization wizard');
        }

        $requestedScopes = ScopeList::fromScopeString($command->getScope())->getScopes();

        $supportedAuthorizationScopes = $this->scopeMapper->getAllScopes();
        $allowedAuthorizationScopes = ScopeList::fromScopes(\array_intersect($requestedScopes, $supportedAuthorizationScopes));

        $supportedAuthenticationScopes = AuthenticationScope::getAllScopes();
        $allowedAuthenticationScopes = ScopeList::fromScopes(\array_intersect($requestedScopes, $supportedAuthenticationScopes));

        $authorization = AppAuthorization::createFromRequest(
            $command->getClientId(),
            $allowedAuthorizationScopes,
            $allowedAuthenticationScopes,
            $app->getCallbackUrl(),
            $command->getState(),
        );

        $this->session->initialize($authorization);
    }
}
