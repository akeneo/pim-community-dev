<?php

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Console\CommandLauncher;
use Doctrine\DBAL\Connection;

/**
 * This class will be removed after 4.0 version
 *
 * @group ce
 */
class RemoveProductEmptyRawValuesIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItRemovesAllEmptyValues()
    {
        $this->getConnection()->executeQuery('DELETE FROM pim_catalog_product');
        $sql = <<<SQL
INSERT INTO pim_catalog_product VALUES
    (NULL, NULL, NULL, NULL, 1, 'product1', '{"name": {"<all_channels>": {"<all_locales>": ""}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product2', '{"name": {"<all_channels>": {"<all_locales>": []}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product3', '{"name": {"<all_channels>": {"<all_locales>": [""]}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product4', '{"name": {"<all_channels>": {"<all_locales>": null}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product5', '{"name": {"<all_channels>": {"<all_locales>": ""}}, "foo": {"<all_channels>": {"<all_locales>": "bar"}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product6', '{"name": {"<all_channels>": {"fr_FR": "", "en_US": "bar"}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product7', '{"name": {"ecommerce": {"<all_locales>": ""}, "mobile": {"<all_locales>": "bar"}}}', NOW(), NOW())
SQL;

        $this->getConnection()->executeQuery($sql);

        $this->reExecuteMigration('_4_0_20190916122239_remove_product_empty_raw_values');

        $this->assertProductRawValuesEquals('product1', '{}');
        $this->assertProductRawValuesEquals('product2', '{}');
        $this->assertProductRawValuesEquals('product3', '{}');
        $this->assertProductRawValuesEquals('product4', '{}');
        $this->assertProductRawValuesEquals('product5', '{"foo": {"<all_channels>": {"<all_locales>": "bar"}}}');
        $this->assertProductRawValuesEquals('product6', '{"name": {"<all_channels>": {"en_US": "bar"}}}');
        $this->assertProductRawValuesEquals('product7', '{"name": {"mobile": {"<all_locales>": "bar"}}}');
    }

    private function getCommandLauncher(): CommandLauncher
    {
        return $this->get('pim_catalog.command_launcher');
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

    private function reExecuteMigration(string $migrationLabel)
    {
        $resultDown = $this->getCommandLauncher()->executeForeground(sprintf('doctrine:migrations:execute %s --down -n', $migrationLabel));
        $this->assertEquals(0, $resultDown->getCommandStatus(), json_encode($resultDown->getCommandOutput()));

        $resultUp = $this->getCommandLauncher()->executeForeground(sprintf('doctrine:migrations:execute %s --up -n', $migrationLabel));
        $this->assertEquals(0, $resultUp->getCommandStatus(), json_encode($resultUp->getCommandOutput()));
    }
}
