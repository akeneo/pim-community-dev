<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * This class will be removed after 4.0 version
 */
class Version_4_0_20200116122239_remove_product_empty_raw_values_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItRemovesAllEmptyValues()
    {
        $this->getConnection()->executeQuery('DELETE FROM pim_catalog_product');
        $sql = <<<SQL
INSERT INTO pim_catalog_product (id, uuid, family_id, product_model_id, family_variant_id, is_enabled, identifier, raw_values, created, updated) VALUES
    (NULL, UUID_TO_BIN('b68859bb-307f-4b52-9e7a-9dfba0a5ae33'), NULL, NULL, NULL, 1, 'product1', '{"name": {"<all_channels>": {"<all_locales>": ""}}}', NOW(), NOW()),
    (NULL, UUID_TO_BIN('19b3d820-0e63-400a-9775-f28ac87151ed'), NULL, NULL, NULL, 1, 'product2', '{"name": {"<all_channels>": {"<all_locales>": []}}}', NOW(), NOW()),
    (NULL, UUID_TO_BIN('8049106b-36c5-4f29-b42b-be7f2448ca2b'), NULL, NULL, NULL, 1, 'product3', '{"name": {"<all_channels>": {"<all_locales>": [""]}}}', NOW(), NOW()),
    (NULL, UUID_TO_BIN('e32698fc-a7fa-4b6d-86db-50ba701a6418'), NULL, NULL, NULL, 1, 'product4', '{"name": {"<all_channels>": {"<all_locales>": null}}}', NOW(), NOW()),
    (NULL, UUID_TO_BIN('762e38cb-59b2-49a5-a1a4-30dd41188375'), NULL, NULL, NULL, 1, 'product5', '{"name": {"<all_channels>": {"<all_locales>": ""}}, "foo": {"<all_channels>": {"<all_locales>": "bar"}}}', NOW(), NOW()),
    (NULL, UUID_TO_BIN('c6c6dd27-425b-4c3c-a0de-ddec28cd6c52'), NULL, NULL, NULL, 1, 'product6', '{"name": {"<all_channels>": {"fr_FR": "", "en_US": "bar"}}}', NOW(), NOW()),
    (NULL, UUID_TO_BIN('78dd88b8-c00d-4260-9794-d49cfa090741'), NULL, NULL, NULL, 1, 'product7', '{"name": {"ecommerce": {"<all_locales>": ""}, "mobile": {"<all_locales>": "bar"}}}', NOW(), NOW())
SQL;

        $this->getConnection()->executeQuery($sql);

        $this->reExecuteMigration($this->getMigrationLabel());

        $this->assertProductRawValuesEquals('product1', '{}');
        $this->assertProductRawValuesEquals('product2', '{}');
        $this->assertProductRawValuesEquals('product3', '{}');
        $this->assertProductRawValuesEquals('product4', '{}');
        $this->assertProductRawValuesEquals('product5', '{"foo": {"<all_channels>": {"<all_locales>": "bar"}}}');
        $this->assertProductRawValuesEquals('product6', '{"name": {"<all_channels>": {"en_US": "bar"}}}');
        $this->assertProductRawValuesEquals('product7', '{"name": {"mobile": {"<all_locales>": "bar"}}}');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function assertProductRawValuesEquals(string $productIdentifier, string $expectedRawValues)
    {
        $result = $this->getConnection()->fetchArray(
            'SELECT raw_values FROM pim_catalog_product WHERE identifier=:identifier',
            ['identifier' => $productIdentifier]
        );
        $this->assertEquals($expectedRawValues, $result[0]);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
