<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\EventSubscriber;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Events\AttributeRemovedEvent;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotifyOnAttributeRemovalEventSubscriberIntegration extends TestCase
{
    private Connection $connection;
    private UserLoader $userLoader;
    private AclLoader $aclLoader;
    private CommandBus $commandBus;
    private ConnectedAppLoader $connectedAppLoader;
    private EventDispatcherInterface $eventDispatcher;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->userLoader = $this->get(UserLoader::class);
        $this->aclLoader = $this->get(AclLoader::class);
        $this->commandBus = $this->get(CommandBus::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->eventDispatcher = $this->get('event_dispatcher');
    }

    /**
     * @throws \Throwable
     * @throws \Doctrine\DBAL\Exception
     */
    public function test_it_notifies_when_an_attribute_removed_event_is_dispatched(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'shopifi',
        );
        $userAdmin = $this->createAdminUser();
        $userWithManageAppAcl = $this->userLoader->createUser('user_with_manage_app_acl', ['userGroupB'], ['ROLE_APP']);
        $userWithoutManageAppAcl = $this->userLoader->createUser('user_without_manage_app_acl', ['userGroupC'], ['ROLE_USER']);
        $owner = $this->getOwnerByConnectedAppId('2677e764-f852-4956-bf9b-1a1ec1b0d145');
        $this->aclLoader->addAclToRoles('akeneo_connectivity_connection_manage_apps', [
            'ROLE_SHOPIFI',
            'ROLE_APP',
            'ROLE_ADMINISTRATOR'
        ]);

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->commandBus->execute(new CreateCatalogCommand(
            $catalogId,
            'Store FR',
            $owner['username'],
        ));

        $this->eventDispatcher->dispatch(new AttributeRemovedEvent($catalogId));

        $this->assertNotificationExistsForUsers([
            $userAdmin->getId(),
            $owner['user_id'],
            $userWithManageAppAcl->getId(),
        ]);

        $this->assertNotificationNotExistsForUsers([
            $userWithoutManageAppAcl->getId(),
        ]);
    }

    /**
     * @param int[] $userIds
     * @throws \Doctrine\DBAL\Exception
     */
    private function assertNotificationExistsForUsers(array $userIds): void
    {
        $userNotificationCount = $this->getUserNotificationCountByUserIds($userIds);
        self::assertEquals(\count($userIds), $userNotificationCount);
    }

    /**
     * @param int[] $userIds
     * @throws \Doctrine\DBAL\Exception
     */
    private function assertNotificationNotExistsForUsers(array $userIds): void
    {
        $userNotificationCount = $this->getUserNotificationCountByUserIds($userIds);
        self::assertEquals(0, $userNotificationCount);
    }

    /**
     * @param int[] $userIds
     * @throws \Doctrine\DBAL\Exception
     */
    private function getUserNotificationCountByUserIds(array $userIds): int
    {
        $query = <<<SQL
            SELECT SUM(grouped_count.nb) FROM (
                SELECT COUNT(*) as nb
                FROM pim_notification_notification
                LEFT JOIN pim_notification_user_notification on pim_notification_notification.id = pim_notification_user_notification.notification
                WHERE
                    message = "pim_notification.attribute_removed.message" 
                    AND user IN (:userIds)
                GROUP BY user
            ) grouped_count
        SQL;

        return (int) $this->connection->fetchOne(
            $query,
            ['userIds' => $userIds],
            ['userIds' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function getOwnerByConnectedAppId(string $connectedAppId): array
    {
        $query = <<<SQL
            SELECT
                   user_id,
                   username
            FROM akeneo_connectivity_connected_app
            INNER JOIN akeneo_connectivity_connection ON akeneo_connectivity_connected_app.connection_code = akeneo_connectivity_connection.code
            INNER JOIN oro_user ON oro_user.id = user_id
            WHERE 
                  akeneo_connectivity_connected_app.id = :connectedAppId
        SQL;

        return $this->connection->fetchAssociative($query, [
            'connectedAppId' => $connectedAppId,
        ]);
    }
}
