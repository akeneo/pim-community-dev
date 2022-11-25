<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_7_0_20221020140000_add_sftp_keys_into_pim_configuration_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221020140000_add_sftp_keys_into_pim_configuration';

    private Connection $connection;

    public function test_it_inserts_keys_into_pim_configuration(): void
    {
        $this->dropKeysIfExist();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->checkKeysExist();
    }

    public function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function dropKeysIfExist(): void
    {
        $this->connection->executeQuery(
            'DELETE FROM pim_configuration WHERE code = :code',
            ['code' => 'SFTP_ASYMMETRIC_KEYS']
        );
    }

    private function checkKeysExist(): void
    {
        $result = $this->connection->executeQuery(
            'SELECT COUNT(1) FROM pim_configuration WHERE code = :code',
            ['code' => 'SFTP_ASYMMETRIC_KEYS']
        );
        $this->assertEquals(1, $result->fetchOne());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }
}
