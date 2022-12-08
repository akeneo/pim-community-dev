<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Doctrine\DBAL\Connection;

class DisableCatalogsOnCurrencyDeactivationTaskletTest extends IntegrationTestCase
{
    private Connection $connection;
    private UserLoader $userLoader;
    private AclLoader $aclLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = static::getContainer()->get('database_connection');
        $this->userLoader = static::getContainer()->get(UserLoader::class);
        $this->aclLoader = static::getContainer()->get(AclLoader::class);
        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogsOnCurrencyDeactivation(): void
    {
        $this->getAuthenticatedInternalApiClient();

        $idCatalogUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idCatalogFR = 'b79b09a3-cb4c-45f8-a086-4f70cc17f521';
        $this->createUser('shopifi');
        $this->createUser('magenta');

        $connectedAppLoader = static::getContainer()->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'shopifi',
        );

        $userWithManageAppAcl = $this->userLoader->createUser('user_with_manage_app_acl', ['userGroupB'], ['ROLE_APP']);
        $owner = $this->getOwnerByConnectedAppId('2677e764-f852-4956-bf9b-1a1ec1b0d145');
        $this->aclLoader->addAclToRoles('akeneo_connectivity_connection_manage_apps', [
            'ROLE_SHOPIFI',
            'ROLE_APP',
            'ROLE_ADMINISTRATOR'
        ]);

        $this->createCatalog($idCatalogUS, 'Store US', $owner['username']);
        $this->createCatalog($idCatalogFR, 'Store FR', $owner['username']);
        $this->enableCatalog($idCatalogUS);
        $this->enableCatalog($idCatalogFR);

        $this->setCatalogProductSelection($idCatalogUS, [
            [
                'field' => 'currencies',
                'operator' => Operator::IN_LIST,
                'value' => ['EUR', 'USD'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($idCatalogFR, [
            [
                'field' => 'currencies',
                'operator' => Operator::IN_LIST,
                'value' => ['USD'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $this->disableCurrency('EUR');
        $this->waitForQueuedJobs();

        $this->assertCatalogIsDisabled($idCatalogUS);
        $this->assertCatalogIsEnabled($idCatalogFR);


        $this->assertNotificationExistsForUsers([
            $owner['user_id'],
            $userWithManageAppAcl->getId(),
        ]);
    }

    private function assertCatalogIsDisabled(string $id): void
    {
        $catalog = $this->getCatalog($id);
        $this->assertFalse($catalog->isEnabled());
    }

    private function assertCatalogIsEnabled(string $id): void
    {
        $catalog = $this->getCatalog($id);
        $this->assertTrue($catalog->isEnabled());
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
    private function getUserNotificationCountByUserIds(array $userIds): int
    {
        $query = <<<SQL
            SELECT SUM(grouped_count.nb) FROM (
                SELECT COUNT(*) as nb
                FROM pim_notification_notification
                LEFT JOIN pim_notification_user_notification on pim_notification_notification.id = pim_notification_user_notification.notification
                WHERE
                    message = "pim_notification.disabled_catalog.message"
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
}
