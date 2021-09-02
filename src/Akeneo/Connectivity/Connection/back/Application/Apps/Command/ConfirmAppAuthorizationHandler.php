<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAppInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\App;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\AppRoleWithScopesFactory;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
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
    private CreateAppInterface $createApp;

    public function __construct(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
        GetAppQueryInterface $getAppQuery,
        CreateUserInterface $createUser,
        CreateUserGroupInterface $createUserGroup,
        CreateConnectionInterface $createConnection,
        AppRoleWithScopesFactory $roleFactory,
        ClientProviderInterface $clientProvider,
        CreateAppInterface $createApp
    ) {
        $this->validator = $validator;
        $this->session = $session;
        $this->getAppQuery = $getAppQuery;
        $this->createUser = $createUser;
        $this->createUserGroup = $createUserGroup;
        $this->createConnection = $createConnection;
        $this->roleFactory = $roleFactory;
        $this->clientProvider = $clientProvider;
        $this->createApp = $createApp;
    }

    public function handle(ConfirmAppAuthorizationCommand $command): App
    {
        $violations = $this->validator->validate($command);
        if (count($violations) > 0) {
            throw new InvalidAppAuthorizationRequest($violations);
        }

        $appId = $command->getClientId();

        $marketplaceApp = $this->getAppQuery->execute($appId);
        if (null === $marketplaceApp) {
            throw new \RuntimeException('App not found');
        }

        /** @var AppAuthorization $appAuthorization */
        $appAuthorization = $this->session->getAppAuthorization($appId);

        /** @var Client $client */
        $client = $this->clientProvider->findClientByAppId($appId);

        $group = $this->createUserGroup->execute($this->generateGroupName($marketplaceApp->getName()));
        $role = $this->roleFactory->createRole($appId, $appAuthorization->scopeList());

        /** @var string $groupName */
        $groupName = $group->getName();
        /** @var string $roleName */
        $roleName = $role->getRole();
        $user = $this->createUser->execute($appId, $appId, $appId, [$groupName], [$roleName]);

        $connection = $this->createConnection->execute(
            $this->generateConnectionCode($appId),
            $marketplaceApp->getName(),
            FlowType::OTHER,
            $client->getId(),
            $user->id(),
        );

        return $this->createApp->execute($marketplaceApp, $appAuthorization->scopeList(), $connection->code());
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
