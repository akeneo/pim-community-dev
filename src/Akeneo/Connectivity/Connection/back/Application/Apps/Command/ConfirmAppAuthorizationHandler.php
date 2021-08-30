<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\AppConnectionProviderInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\AppUserProviderInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConfirmAppAuthorizationHandler
{
    private ValidatorInterface $validator;
    private AppAuthorizationSessionInterface $session;
    private GetAppQueryInterface $getAppQuery;
    private AppUserProviderInterface $appUserProvider;
    private AppConnectionProviderInterface $appConnectionProvider;

    public function __construct(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
        GetAppQueryInterface $getAppQuery,
        AppUserProviderInterface $appUserProvider,
        AppConnectionProviderInterface $appConnectionProvider
    ) {
        $this->validator = $validator;
        $this->session = $session;
        $this->getAppQuery = $getAppQuery;
        $this->appUserProvider = $appUserProvider;
        $this->appConnectionProvider = $appConnectionProvider;
    }

    public function handle(ConfirmAppAuthorizationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (count($violations) > 0) {
            throw new InvalidAppAuthorizationRequest($violations);
        }

        $clientId = $command->getClientId();

        $appAuthorization = $this->session->getAppAuthorization($command->getClientId());

        $app = $this->getAppQuery->execute($clientId);
        if (null === $app) {
            throw new \RuntimeException('App not found');
        }

        $user = $this->appUserProvider->createUser($app->getName(), $appAuthorization->scopeList());

        $connection = $this->appConnectionProvider->createAppConnection($app->getName(), $clientId, $user->getId());

        dd($connection);
    }


}
