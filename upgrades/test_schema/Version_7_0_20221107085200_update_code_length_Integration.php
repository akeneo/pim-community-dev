<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

class Version_7_0_20221107085200_update_code_length_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221107085200_update_code_length';

    public function test_it_modify_columns_and_keep_the_data(): void
    {
        $query = <<<SQL
        ALTER TABLE pim_catalog_identifier_generator
        MODIFY `code` varchar(100) NOT NULL;
SQL;

        $this->getConnection()->executeQuery($query);
        $this->assertCodeColumnLength(100);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertCodeColumnLength(255);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertCodeColumnLength(int $columnLength): void
    {
        $columns = $this->getConnection()->getSchemaManager()->listTableColumns('pim_catalog_identifier_generator');

        Assert::assertEquals($columnLength, $columns['code']?->getLength());
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
