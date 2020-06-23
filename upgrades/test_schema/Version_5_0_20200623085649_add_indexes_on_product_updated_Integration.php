<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class Version_5_0_20200623085649_add_indexes_on_product_updated_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200623085649_add_indexes_on_product_updated';

    /** @test */
    public function it_adds_indexes_on_product_update_date(): void
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertIndexExists('idx_product_updated', 'pim_catalog_product');
        $this->assertIndexExists('idx_product_model_updated', 'pim_catalog_product_model');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dropIndexIfExists('idx_product_updated', 'pim_catalog_product');
        $this->dropIndexIfExists('idx_product_model_updated', 'pim_catalog_product_model');
    }

    private function findIndex($index, $table): ?array
    {
        $result = $this
            ->get('database_connection')->executeQuery(<<<SQL
               SHOW INDEX FROM $table WHERE KEY_NAME = '$index';
            SQL
            )
            ->fetch(\PDO::FETCH_ASSOC)
        ;

        if ($result === false) {
            return null;
        }

        return $result;
    }

    private function assertIndexExists($index, $table): void
    {
        $indexInformation = $this->findIndex($index, $table);
        Assert::assertNotNull($indexInformation, sprintf('The index "%s" does not exists on table "%s"', $index, $table));
    }

    private function dropIndexIfExists($index, $table): void
    {
        $indexInformation = $this->findIndex($index, $table);

        if ($indexInformation) {
            $this->get('database_connection')->executeUpdate(<<<SQL
                DROP INDEX $index ON $table;
            SQL
            );
        }
    }
}
