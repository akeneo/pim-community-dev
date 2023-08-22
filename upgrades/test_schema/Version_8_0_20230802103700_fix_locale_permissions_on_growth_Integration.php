<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230802103700_fix_locale_permissions_on_growth_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230802103700_fix_locale_permissions_on_growth';

    use ExecuteMigrationTrait;

    private Connection $connection;
    private ?ConnectedAppLoader $connectedAppLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_add_missing_lines_and_only_them()
    {
        $appCode = 'test_perm';
        $groupName = 'app_' . $appCode;

        $lineCountQuery = 'SELECT COUNT(*) FROM pimee_security_locale_access';
        $missingLineQuery = '
            SELECT COUNT(*) 
            FROM pimee_security_locale_access 
            WHERE user_group_id = (SELECT id FROM oro_access_group WHERE name = :group_name LIMIT 1)
              AND view_products = 1
              AND edit_products = 1
        ';

        $oldLineCount = $this->connection->executeQuery($lineCountQuery)->fetchOne();
        $oldMissingLineCount = $this->connection
            ->executeQuery($missingLineQuery, [':group_name' => $groupName])
            ->fetchOne();

        self::assertEquals(0, $oldMissingLineCount);

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '0d462f0a-ce73-459d-a4b5-8fd444a980b8',
            $appCode
        );
        $this->updatePermissionsForAGroup($groupName);

        $this->reExecuteMigration(self::MIGRATION_NAME);

        $newLineCount = $this->connection->executeQuery($lineCountQuery)->fetchOne();
        $newMissingLineCount = $this->connection
            ->executeQuery($missingLineQuery, [':group_name' => $groupName])
            ->fetchOne();

        $expectedNewlineCount = $this->getActiveLocaleCount();

        self::assertEquals($expectedNewlineCount, $newLineCount - $oldLineCount);
        self::assertEquals($expectedNewlineCount, $newMissingLineCount);
    }

    private function updatePermissionsForAGroup($groupName)
    {
        $this->connection->executeQuery('
            UPDATE oro_access_group SET default_permissions = \'{"locale_edit": true, "locale_view": true,
                "category_own": true, "category_edit": true, "category_view": true,
                "attribute_group_edit": true, "attribute_group_view": true}\'
            WHERE name LIKE :group_name
        ', [':group_name' => $groupName]);
    }

    private function getActiveLocaleCount()
    {
        return $this->connection->executeQuery(
            'SELECT COUNT(*) FROM pim_catalog_locale WHERE is_activated=1'
        )->fetchOne();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
