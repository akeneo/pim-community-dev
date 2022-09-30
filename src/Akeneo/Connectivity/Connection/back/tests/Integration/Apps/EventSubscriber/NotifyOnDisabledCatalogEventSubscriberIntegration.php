<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\EventSubscriber;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Events\InvalidCatalogDisabledEvent;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotifyOnDisabledCatalogEventSubscriberIntegration extends TestCase
{
    private Connection $connection;
    private UserLoader $userLoader;
    private AclLoader $aclLoader;
    private CommandBus $commandBus;
    private EventDispatcher $eventDispatcher;

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
        $this->eventDispatcher = $this->get('event_dispatcher');
    }

    /**
     * @throws \Throwable
     * @throws \Doctrine\DBAL\Exception
     */
    public function test_it_notify_when_an_invalid_catalog_disabled_event_is_dispatched(): void
    {
        $userAdmin = $this->createAdminUser();
        $userCatalogOwner = $this->userLoader->createUser('user_catalog_owner', ['userGroupA'], ['ROLE_APP_A']);
        $userWithManageAppAcl = $this->userLoader->createUser('user_with_manage_app_acl', ['userGroupB'], ['ROLE_APP_B']);
        $userWithoutManageAppAcl = $this->userLoader->createUser('user_without_manage_app_acl', ['userGroupC'], ['ROLE_USER']);

        $this->aclLoader->addAclToRoles('akeneo_connectivity_connection_manage_apps', [
            'ROLE_APP_A',
            'ROLE_APP_B',
            'ROLE_ADMINISTRATOR'
        ]);

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->commandBus->execute(new CreateCatalogCommand(
            $catalogId,
            'Store FR',
            'user_catalog_owner',
        ));

        $this->eventDispatcher->dispatch(new InvalidCatalogDisabledEvent($catalogId));

        $this->assertNotificationExistsForUsers([
            $userAdmin->getId(),
            $userCatalogOwner->getId(),
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
            SELECT COUNT(*)
            FROM pim_notification_notification
            LEFT JOIN pim_notification_user_notification on pim_notification_notification.id = pim_notification_user_notification.notification
            WHERE
                message = "pim_notification.disabled_catalog.message" 
                AND user IN (:userIds)
        SQL;

        return (int) $this->connection->fetchOne(
            $query,
            ['userIds' => $userIds],
            ['userIds' => Connection::PARAM_INT_ARRAY]
        );
    }
}
