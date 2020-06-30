<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_5_0_20200630044018_create_communication_channel_announcements_viewed_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    /** @var Connection */
    private $dbalConnection;

    /** @var AbstractSchemaManager */
    private $schemaManager;

    private const MIGRATION_LABEL = '_5_0_20200630044018_create_communication_channel_announcements_viewed_table';

    public function test_it_creates_the_communication_channel_announcements_viewed_table(): void
    {
        $this->dbalConnection->executeQuery('DROP TABLE IF EXISTS akeneo_communication_channel_announcements_viewed');
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->schemaManager->tablesExist('akeneo_communication_channel_announcements_viewed'));
        $expectedColumnsAndTypes = [
            'announcement_id' => 'string',
            'user_id'         => 'integer',
        ];

        $tableColumns = $this->schemaManager->listTableColumns('akeneo_communication_channel_announcements_viewed');
        Assert::assertCount(count($expectedColumnsAndTypes), $tableColumns);

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
