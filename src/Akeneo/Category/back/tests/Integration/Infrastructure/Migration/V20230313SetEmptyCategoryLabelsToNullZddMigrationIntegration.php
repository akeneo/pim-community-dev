<?php

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Migration;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Infrastructure\Migration\V20230313SetEmptyCategoryLabelsToNullZddMigration;
use Akeneo\Test\Integration\Configuration;

class V20230313SetEmptyCategoryLabelsToNullZddMigrationIntegration extends CategoryTestCase
{
    private V20230313SetEmptyCategoryLabelsToNullZddMigration $migration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migration = $this->get(V20230313SetEmptyCategoryLabelsToNullZddMigration::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_sets_empty_labels_to_null(): void
    {
        $connection = $this->get('database_connection');
        foreach(range(1,100) as $index) {
            $connection->executeStatement(
            <<<SQL
            INSERT INTO akeneo_pim_test.pim_catalog_category_translation
            (foreign_key, label, locale)
            VALUES(
                (
                    SELECT id 
                    FROM pim_catalog_category 
                    WHERE code = 'master'
                ), '', :key);
            SQL,
            ['key' => "fr_FR_$index"]
            );
        }

        $this->assertEquals(100, $this->test_is_category_label_an_empty_string());
        $this->migration->migrate();
        $this->assertEquals(0, $this->test_is_category_label_an_empty_string());
    }

    private function test_is_category_label_an_empty_string(): int
    {
        $connection = $this->get('database_connection');
        return (int) $connection->fetchOne(
            <<<SQL
                SELECT count(*)
                FROM pim_catalog_category_translation
                WHERE label = ''
            SQL
        );
    }
}
