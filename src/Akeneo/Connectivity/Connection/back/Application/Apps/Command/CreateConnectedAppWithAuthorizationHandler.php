<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\AppRoleWithScopesFactoryInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedAppInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Event\AppUserGroupCreated;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectedAppWithAuthorizationHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private AppAuthorizationSessionInterface $session,
        private GetAppQueryInterface $getAppQuery,
        private CreateUserInterface $createUser,
        private CreateUserGroupInterface $createUserGroup,
        private CreateConnectionInterface $createConnection,
        private AppRoleWithScopesFactoryInterface $appRoleWithScopesFactory,
        private ClientProviderInterface $clientProvider,
        private CreateConnectedAppInterface $createConnectedApp,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(CreateConnectedAppWithAuthorizationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (\count($violations) > 0) {
            throw new InvalidAppAuthorizationRequestException($violations);
        }

        $appId = $command->getClientId();
        // limit the random code to 30chars long because it's the limit imposed by UserGroup name constraints.
        $randomCode = $this->generateRandomCode(30, 'app_');

        $marketplaceApp = $this->getAppQuery->execute($appId);
        if (null === $marketplaceApp) {
            throw new \LogicException('App should exists when validating the authorization wizard');
        }

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

        $userId = $this->createUser->execute(
            $randomCode,
            \strtr($marketplaceApp->getName(), '<>&"', '____'),
            [$group->getName()],
            [$role->getRole()],
            $appId,
        );

        $connection = $this->createConnection->execute(
            $randomCode,
            $marketplaceApp->getName(),
            FlowType::OTHER,
            $client->getId(),
            $userId,
        );

        $this->createConnectedApp->execute(
            $marketplaceApp,
            $appAuthorization->getAuthorizationScopes()->getScopes(),
            $connection->code(),
            $group->getName(),
            $randomCode
        );

        $this->eventDispatcher->dispatch(new AppUserGroupCreated($group->getName()), AppUserGroupCreated::class);
    }

    private function generateRandomCode(int $maxLength = 30, string $prefix = ''): string
    {
        return \substr(\sprintf('%s%s', $prefix, \base_convert(\bin2hex(\random_bytes(16)), 16, 36)), 0, $maxLength);
    }
}
