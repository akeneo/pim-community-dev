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
class Version_5_0_20200901102010_add_connection_webhook_columns_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200901102010_add_connection_webhook_columns';

    /** @var Connection */
    private $dbalConnection;

    /** @var AbstractSchemaManager */
    private $schemaManager;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->schemaManager = $this->dbalConnection->getSchemaManager();
    }

    public function test_it_add_the_webhook_columns(): void
    {
        $this->ensureConnectionWebhookColumnsDoesNotExist();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertConnectionTableHasColumns([
            'webhook_url' => 'string',
            'webhook_secret' => 'string',
            'webhook_enabled' => 'boolean',
        ]);
    }

    private function ensureConnectionWebhookColumnsDoesNotExist(): void
    {
        $this->dbalConnection->executeQuery(<<<SQL
ALTER TABLE akeneo_connectivity_connection
DROP COLUMN webhook_url,
DROP COLUMN webhook_secret,
DROP COLUMN webhook_enabled;
SQL);
    }

    private function assertConnectionTableHasColumns(array $expectedColumnsAndTypes): void
    {
        $tableColumns = $this->schemaManager->listTableColumns('akeneo_connectivity_connection');

        $actualColumnsAndTypes = [];
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] = $actualColumn->getType()->getName();
        }

        Assert::assertEquals(array_merge($actualColumnsAndTypes, $expectedColumnsAndTypes), $actualColumnsAndTypes);
    }
}
