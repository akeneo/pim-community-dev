<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\AppRoleWithScopesFactoryInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedAppInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\AccessDeniedException;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAppWithAuthorizationHandler
{
    private ValidatorInterface $validator;
    private AppAuthorizationSessionInterface $session;
    private GetAppQueryInterface $getAppQuery;
    private CreateUserInterface $createUser;
    private CreateUserGroupInterface $createUserGroup;
    private CreateConnectionInterface $createConnection;
    private AppRoleWithScopesFactoryInterface $appRoleWithScopesFactory;
    private ClientProviderInterface $clientProvider;
    private CreateConnectedAppInterface $createApp;
    private SecurityFacade $security;

    public function __construct(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
        GetAppQueryInterface $getAppQuery,
        CreateUserInterface $createUser,
        CreateUserGroupInterface $createUserGroup,
        CreateConnectionInterface $createConnection,
        AppRoleWithScopesFactoryInterface $appRoleWithScopesFactory,
        ClientProviderInterface $clientProvider,
        CreateConnectedAppInterface $createApp,
        SecurityFacade $security,
    ) {
        $this->validator = $validator;
        $this->session = $session;
        $this->getAppQuery = $getAppQuery;
        $this->createUser = $createUser;
        $this->createUserGroup = $createUserGroup;
        $this->createConnection = $createConnection;
        $this->appRoleWithScopesFactory = $appRoleWithScopesFactory;
        $this->clientProvider = $clientProvider;
        $this->createApp = $createApp;
        $this->security = $security;
    }

    public function handle(CreateAppWithAuthorizationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (count($violations) > 0) {
            throw new InvalidAppAuthorizationRequestException($violations);
        }

        $appId = $command->getClientId();
        // limit the random code to 30chars long because it's the limit imposed by UserGroup name constraints.
        $randomCode = $this->generateRandomCode(30, 'app_');

        $app = $this->getAppQuery->execute($appId);
        if (null === $app) {
            throw new \LogicException('App should exists when validating the authorization wizard');
        }

        $this->denyAccessUnlessGrantedToManage($app);

        $appAuthorization = $this->session->getAppAuthorization($appId);
        if (null === $appAuthorization) {
            throw new \LogicException('AppAuthorization should exists in the session for the given app');
        }

        $client = $this->clientProvider->findClientByAppId($appId);
        if (null === $client) {
            throw new \LogicException('OAuth client should exists for the given app');
        }

        $group = $this->createUserGroup->execute($randomCode);
        if (null === $group->getName()) {
            throw new \LogicException('The user group should have a name, got null.');
        }

        $role = $this->appRoleWithScopesFactory->createRole($appId, $appAuthorization->getAuthorizationScopes()->getScopes());
        if (null === $role->getRole()) {
            throw new \LogicException('The user role should have a role code, like ROLE_*, got null.');
        }

        $user = $this->createUser->execute(
            $randomCode,
            $app->getName(),
            ' ',
            [$group->getName()],
            [$role->getRole()]
        );

        $connection = $this->createConnection->execute(
            $randomCode,
            $app->getName(),
            FlowType::OTHER,
            $client->getId(),
            $user->id(),
        );

        $this->createApp->execute(
            $app,
            $appAuthorization->getAuthorizationScopes()->getScopes(),
            $connection->code(),
            $group->getName()
        );
    }

    private function generateRandomCode(int $maxLength = 30, string $prefix = ''): string
    {
        return substr(sprintf('%s%s', $prefix, base_convert(bin2hex(random_bytes(16)), 16, 36)), 0, $maxLength);
    }

    private function denyAccessUnlessGrantedToManage(App $app): void
    {
        if (
            !$app->isTestApp() &&
            !$this->security->isGranted('akeneo_connectivity_connection_manage_apps')
        ) {
            throw new AccessDeniedException();
        }

        if (
            $app->isTestApp() &&
            !$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')
        ) {
            throw new AccessDeniedException();
        }
    }
}
