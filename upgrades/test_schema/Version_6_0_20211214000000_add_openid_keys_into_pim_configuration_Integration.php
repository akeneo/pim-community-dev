<?php
declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveAsymmetricKeysQuery;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211214000000_add_openid_keys_into_pim_configuration_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211214000000_add_openid_keys_into_pim_configuration';

    private Connection $connection;

    public function test_it_inserts_openid_keys_into_pim_configuration(): void
    {
        $this->dropOpenIdKeysIfExist();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->checkOpenIdKeysExist();
    }

    public function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function dropOpenIdKeysIfExist(): void
    {
        $this->connection->executeQuery(
            'DELETE FROM pim_configuration WHERE code = :code',
            ['code' => SaveAsymmetricKeysQuery::OPTION_CODE]
        );
    }

    private function checkOpenIdKeysExist(): void
    {
        $result = $this->connection->executeQuery(
            'SELECT COUNT(1) FROM pim_configuration WHERE code = :code',
            ['code' => SaveAsymmetricKeysQuery::OPTION_CODE]
        );
        $this->assertEquals(1, $result->fetchOne());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }
}
