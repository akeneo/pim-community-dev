<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\AppRoleWithScopesFactory;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
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
    private CreateConnectionInterface $createConnection;
    private CreateUserInterface $createUser;
    private CreateUserGroupInterface $createUserGroup;
    private AppRoleWithScopesFactory $roleFactory;
    private ClientProviderInterface $clientProvider;

    public function __construct(
        ValidatorInterface               $validator,
        AppAuthorizationSessionInterface $session,
        GetAppQueryInterface             $getAppQuery,
        CreateUserInterface              $createUser,
        CreateUserGroupInterface         $createUserGroup,
        CreateConnectionInterface        $createConnection,
        AppRoleWithScopesFactory         $roleFactory,
        ClientProviderInterface $clientProvider
    ) {
        $this->validator = $validator;
        $this->session = $session;
        $this->getAppQuery = $getAppQuery;
        $this->createUser = $createUser;
        $this->createUserGroup = $createUserGroup;
        $this->createConnection = $createConnection;
        $this->roleFactory = $roleFactory;
        $this->clientProvider = $clientProvider;
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

        $appId = $app->getId();

        $group = $this->createUserGroup->execute($this->generateGroupName($app->getName()));
        $role = $this->roleFactory->createRole($appId, $appAuthorization->scopeList());

        $user = $this->createUser->execute(
            $appId,
            $appId,
            $appId,
            [$group->getName()],
            [$role->getRole()],
        );

        $client = $this->clientProvider->findClientByAppId($appId);
        if (null === $client) {
            throw new \RuntimeException("No client found with client id $appId");
        }

        $connection = $this->createConnection->execute(
            $this->generateConnectionCode($appId),
            $app->getName(),
            FlowType::OTHER,
            $client->getId(),
            $user->id(),
        );

        dd($connection);
    }

    /**
     * Generate unique connection code that match ConnectionCode constraints
     * (unique and only between 3 and 100 chars. long)
     * @see ConnectionCode
     */
    private function generateConnectionCode(string $appId): string
    {
        return base64_encode($appId);
    }

    /**
     * Generate unique user group name that match UserGroup constraints (only 30chars. long)
     */
    private function generateGroupName(string $appName): string
    {
        return time() . '_' . substr($appName, 0, 19);
    }
}
