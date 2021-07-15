<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

final class Version_6_0_20210713134208_create_table_invited_users_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $dbalConnection;
    private ?AbstractSchemaManager $schemaManager;

    private const MIGRATION_LABEL = '_6_0_20210713134208_create_table_invited_users';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->schemaManager = $this->dbalConnection->getSchemaManager();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_the_table_invited_users(): void
    {
        $this->dbalConnection->executeQuery('DROP TABLE IF EXISTS akeneo_free_trial_invited_user;');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->schemaManager->tablesExist('akeneo_free_trial_invited_user'));
    }
}
