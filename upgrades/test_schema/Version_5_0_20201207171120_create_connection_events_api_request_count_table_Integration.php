<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_5_0_20201207171120_create_connection_events_api_request_count_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $dbalConnection;
    private AbstractSchemaManager $schemaManager;

    private const MIGRATION_LABEL = '_5_0_20201207171120_create_connection_events_api_request_count_table';

    public function test_it_creates_the_audit_error_table(): void
    {
        $this->dbalConnection->executeQuery('DROP TABLE IF EXISTS akeneo_connectivity_connection_events_api_request_count');
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->schemaManager->tablesExist('akeneo_connectivity_connection_events_api_request_count'));
        $expectedColumnsAndTypes = [
            'event_minute' => 'integer',
            'event_count' => 'integer',
            'updated' => 'datetime',
        ];

        $tableColumns = $this->schemaManager->listTableColumns('akeneo_connectivity_connection_events_api_request_count');
        $this->assertCount(count($expectedColumnsAndTypes), $tableColumns);

        $actualColumnsAndTypes = [];
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] =  $actualColumn->getType()->getName();
        }
        Assert::assertEquals($expectedColumnsAndTypes, $actualColumnsAndTypes);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->schemaManager  = $this->dbalConnection->getSchemaManager();
    }
}
