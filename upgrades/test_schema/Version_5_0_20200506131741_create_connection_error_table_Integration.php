<?php
declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_5_0_20200506131741_create_connection_error_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    /** @var Connection */
    private $dbalConnection;

    /** @var AbstractSchemaManager */
    private $schemaManager;

    private const MIGRATION_LABEL = '_5_0_20200506131741_create_connection_error_count_table';

    public function test_it_creates_the_audit_error_count_table(): void
    {
        $this->dbalConnection->executeQuery('DROP TABLE IF EXISTS akeneo_connectivity_connection_audit_error');
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->schemaManager->tablesExist('akeneo_connectivity_connection_audit_error'));
        $expectedColumnsAndTypes = [
            'connection_code' => 'string',
            'error_datetime'  => 'datetime',
            'error_count'     => 'integer',
            'error_type'      => 'string',
            'updated'         => 'datetime',
        ];

        $tableColumns = $this->schemaManager->listTableColumns('akeneo_connectivity_connection_audit_error');
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
