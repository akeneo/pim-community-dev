<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * This class will be removed after 4.0 version
 */
class Version_5_0_20200224123917_remove_product_model_empty_amount_values_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItRemovesAllEmptyValues()
    {
        $this->getConnection()->executeQuery('DELETE FROM pim_catalog_product_model');
        $familySearch = $this->getConnection()->fetchArray('SELECT id FROM pim_catalog_family_variant LIMIT 1');
        $familyId = $familySearch[0];
        $sql = <<<SQL
INSERT INTO pim_catalog_product_model VALUES
    (NULL, NULL, :familyId, 'pm1', '{"a_metric": {"<all_channels>": {"<all_locales>": null}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm2', '{"a_metric": {"<all_channels>": {"<all_locales>": {}}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm3', '{"a_metric": {"<all_channels>": {"<all_locales>": {"unit": null, "amount": null}}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm4', '{"a_metric": {"<all_channels>": {"<all_locales>": {"unit": "WATT", "amount": 5000}}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm5', '{"a_metric": {"<all_channels>": {"fr_FR": {"unit": null, "amount": null}, "en_US": {"unit": "WATT", "amount": 5000}}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm6', '{"a_metric": {"ecommerce": {"<all_locales>": {"unit": null, "amount": null}}, "mobile": {"<all_locales>": {"unit": "WATT", "amount": 5000}}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm7', '{"a_price": {"<all_channels>": {"<all_locales>": null}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm8', '{"a_price": {"<all_channels>": {"<all_locales>": []}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm9', '{"a_price": {"<all_channels>": {"<all_locales>": [{"amount": null, "currency": "USD"}]}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm10', '{"a_price": {"<all_channels>": {"<all_locales>": [{"amount": "1329.00", "currency": "USD"}]}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm11', '{"a_price": {"<all_channels>": {"<all_locales>": [{"amount": "1329.00", "currency": "USD"}, {"amount": "1329.00", "currency": "EUR"}]}}}', NOW(), NOW(), 0, 0, 0, 0),
    (NULL, NULL, :familyId, 'pm12', '{"a_price": {"<all_channels>": {"<all_locales>": [{"amount": "1329.00", "currency": "USD"}, {"amount": null, "currency": "EUR"}]}}}', NOW(), NOW(), 0, 0, 0, 0)
SQL;

        $this->getConnection()->executeQuery($sql, ['familyId' => $familyId]);

        $this->reExecuteMigration($this->getMigrationLabel());

        $this->assertProductModelRawValuesEquals('pm1', '{}');
        $this->assertProductModelRawValuesEquals('pm2', '{}');
        $this->assertProductModelRawValuesEquals('pm3', '{}');
        $this->assertProductModelRawValuesEquals('pm4', '{"a_metric": {"<all_channels>": {"<all_locales>": {"unit": "WATT", "amount": 5000}}}}');
        $this->assertProductModelRawValuesEquals('pm5', '{"a_metric": {"<all_channels>": {"en_US": {"unit": "WATT", "amount": 5000}}}}');
        $this->assertProductModelRawValuesEquals('pm6', '{"a_metric": {"mobile": {"<all_locales>": {"unit": "WATT", "amount": 5000}}}}');
        $this->assertProductModelRawValuesEquals('pm7', '{}');
        $this->assertProductModelRawValuesEquals('pm8', '{}');
        $this->assertProductModelRawValuesEquals('pm9', '{}');
        $this->assertProductModelRawValuesEquals('pm10', '{"a_price": {"<all_channels>": {"<all_locales>": [{"amount": "1329.00", "currency": "USD"}]}}}');
        $this->assertProductModelRawValuesEquals('pm11', '{"a_price": {"<all_channels>": {"<all_locales>": [{"amount": "1329.00", "currency": "USD"}, {"amount": "1329.00", "currency": "EUR"}]}}}');
        $this->assertProductModelRawValuesEquals('pm12', '{"a_price": {"<all_channels>": {"<all_locales>": [{"amount": "1329.00", "currency": "USD"}]}}}');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function assertProductModelRawValuesEquals(string $productModelCode, string $expectedRawValues)
    {
        $result = $this->getConnection()->fetchArray(
            'SELECT raw_values FROM pim_catalog_product_model WHERE code=:code',
            ['code' => $productModelCode]
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
