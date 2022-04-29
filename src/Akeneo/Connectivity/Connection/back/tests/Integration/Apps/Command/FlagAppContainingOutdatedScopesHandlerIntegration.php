<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesHandler;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByIdQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagAppContainingOutdatedScopesHandlerIntegration extends TestCase
{
    private FlagAppContainingOutdatedScopesHandler $handler;
    private ConnectedAppLoader $connectedAppLoader;
    private FindOneConnectedAppByIdQuery $findOneConnectedAppByIdQuery;
    private Connection $connection;
    private UserLoader $userLoader;
    private AclLoader $aclLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->get(FlagAppContainingOutdatedScopesHandler::class);
        $this->findOneConnectedAppByIdQuery = $this->get(FindOneConnectedAppByIdQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connection = $this->get('database_connection');
        $this->userLoader = $this->get(UserLoader::class);
        $this->aclLoader = $this->get(AclLoader::class);
    }

    public function test_it_flags_connected_app_as_with_outdated_scopes(): void
    {
        $userA = $this->userLoader->createUser('userA', ['userGroupA'], ['ROLE_APP_A']);
        $userB = $this->userLoader->createUser('userB', ['userGroupA'], ['ROLE_APP_A']);
        $this->aclLoader->addAclToRoles('akeneo_connectivity_connection_manage_apps', ['ROLE_APP_A']);

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'outdated_app_id',
            'outdated_app_code',
            ['write_categories', 'read_channel_localization', 'read_products']
        );

        $connectedApp = $this->findOneConnectedAppByIdQuery->execute('outdated_app_id');

        $this->handler->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'write_categories read_products random noise read_channel_settings',
        ));

        $this->assertAppIsFlaggedWithOutdatedScopes('outdated_app_id');
        $this->assertNotificationExistsForUsers([$userA->getId(), $userB->getId()]);
    }

    private function assertAppIsFlaggedWithOutdatedScopes(string $appId): void
    {
        $updatedApp = $this->findOneConnectedAppByIdQuery->execute($appId);

        self::assertTrue($updatedApp->hasOutdatedScopes());
    }

    /**
     * @param int[] $userIds
     */
    private function assertNotificationExistsForUsers(array $userIds): void
    {
        $query = <<<SQL
        SELECT COUNT(*)
        FROM pim_notification_notification
        LEFT JOIN pim_notification_user_notification on pim_notification_notification.id = pim_notification_user_notification.notification
        WHERE message = "pim_notification.connected_app_authorizations.message" 
          AND user IN (:userIds)
        SQL;

        $userNotificationCount = (int) $this->connection->fetchOne(
            $query,
            ['userIds' => $userIds],
            ['userIds' => Connection::PARAM_INT_ARRAY]
        );

        self::assertEquals(\count($userIds), $userNotificationCount);
    }
}
