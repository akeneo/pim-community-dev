<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\ServiceApi\Service;

use Akeneo\Connectivity\Connection\Application\Apps\AppRoleWithScopesFactoryInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedAppInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Connectivity\Connection\ServiceApi\Model\ConnectedAppWithValidToken;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectedAppFactory
{
    public function __construct(
        private OAuthStorage $OAuthStorage,
        private ClientProviderInterface $clientProvider,
        private CreateUserGroupInterface $createUserGroup,
        private AppRoleWithScopesFactoryInterface $appRoleWithScopesFactory,
        private CreateUserInterface $createUser,
        private CreateConnectionInterface $createConnection,
        private CreateConnectedAppInterface $createApp,
        private DbalConnection $dbalConnection,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function createFakeConnectedAppWithValidToken(
        string $id,
        string $code,
        array $scopes = ['read_products'],
    ): ConnectedAppWithValidToken {
        $app = $this->createApp($id, $code);

        $client = $this->clientProvider->findOrCreateClient($app);
        $group = $this->createUserGroup->execute(\sprintf('app_%s', $code));
        $role = $this->appRoleWithScopesFactory->createRole(
            $code,
            $scopes,
        );
        $userId = $this->createUser->execute(
            $code,
            $app->getName(),
            [$group->getName()],
            [$role->getRole()],
            $id,
        );
        $connection = $this->createConnection->execute(
            $code,
            $app->getName(),
            FlowType::OTHER,
            $client->getId(),
            $userId,
        );
        $this->createApp->execute(
            $app,
            $scopes,
            $connection->code(),
            $group->getName(),
            $code,
        );

        $user = $this->findConnectionUser($code);

        $this->OAuthStorage->createAuthCode($code, $client, $user, '', null);
        /** @var TokenInterface $token */
        $token = $this->OAuthStorage->createAccessToken($code, $client, $user, null, \implode(' ', $scopes));

        return new ConnectedAppWithValidToken(
            $id,
            $code,
            $token->getToken(),
        );
    }

    private function createApp(string $id, string $code): App
    {
        return App::fromWebMarketplaceValues([
            'id' => $id,
            'name' => $code,
            'logo' => '/bundles/akeneoconnectivityconnection/img/app-prototype.png',
            'author' => 'Akeneo',
            'url' => 'http://marketplace.akeneo.com/foo',
            'categories' => ['ecommerce'],
            'activate_url' => 'http://example.com/activate',
            'callback_url' => 'http://example.com/callback',
            'connected' => true,
            'isPending' => false,
        ]);
    }

    private function findConnectionUser(string $code): UserInterface
    {
        $query = <<<SQL
SELECT user_id
FROM akeneo_connectivity_connection
WHERE code = :code
SQL;

        $id = $this->dbalConnection->fetchOne($query, [
            'code' => $code,
        ]);

        return $this->userRepository->findOneById($id);
    }
}
