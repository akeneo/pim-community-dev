<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\AttributeGrid\TestCase;
use Doctrine\DBAL\Connection;

class Version_7_0_20220323110749_remove_multi_select_reference_data_asset_attributes_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220323110749_remove_multi_select_reference_data_asset_attributes';

    /**
     * @test
     */
    public function it_remove_only_asset_reference_data_attribute_not_used_in_product()
    {
        $this->createReferenceDataAttribute('an_asset_reference_data_attribute', 'assets', false, false);
        $this->createReferenceDataAttribute('an_scoped_and_localized_asset_reference_data_attribute', 'assets', true, true);
        $this->createReferenceDataAttribute('an_scoped_asset_reference_data_attribute', 'assets', true, false);
        $this->createReferenceDataAttribute('an_localized_asset_reference_data_attribute', 'assets', false, false);
        $this->createReferenceDataAttribute('an_fabrics_reference_data_attribute', 'fabrics', false, false);

        $this->assertTrue($this->attributeExists('an_asset_reference_data_attribute'));
        $this->assertTrue($this->attributeExists('an_scoped_and_localized_asset_reference_data_attribute'));
        $this->assertTrue($this->attributeExists('an_scoped_asset_reference_data_attribute'));
        $this->assertTrue($this->attributeExists('an_localized_asset_reference_data_attribute'));
        $this->assertTrue($this->attributeExists('an_fabrics_reference_data_attribute'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFalse($this->attributeExists('an_asset_reference_data_attribute'));
        $this->assertFalse($this->attributeExists('an_scoped_and_localized_asset_reference_data_attribute'));
        $this->assertFalse($this->attributeExists('an_scoped_asset_reference_data_attribute'));
        $this->assertFalse($this->attributeExists('an_localized_asset_reference_data_attribute'));
        $this->assertTrue($this->attributeExists('an_fabrics_reference_data_attribute'));
    }

    /**
     * @test
     */
    public function it_does_not_remove_asset_reference_data_attribute_used_in_product()
    {
        $this->createReferenceDataAttribute('an_asset_reference_data_attribute', 'assets', false, false);
        $this->createReferenceDataAttribute('an_localized_asset_reference_data_attribute', 'assets', false, false);

        $this->assertTrue($this->attributeExists('an_asset_reference_data_attribute'));
        $this->assertTrue($this->attributeExists('an_localized_asset_reference_data_attribute'));

        $this->createProduct('a_product');
        $this->updateProduct('a_product', 'an_asset_reference_data_attribute', null, null, 'a value');
        $this->updateProduct('a_product', 'an_localized_asset_reference_data_attribute', 'ecommerce', 'en_US', 'another value');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->attributeExists('an_asset_reference_data_attribute'));
        $this->assertTrue($this->attributeExists('an_localized_asset_reference_data_attribute'));
    }

    private function createProduct(string $identifier)
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function updateProduct(
        string $productIdentifier,
        string $attributeCode,
        ?string $channel,
        ?string $locale,
        string $value
    ): void {
        $channelReference = $channel ?? '<all_channels>';
        $localeReference = $locale ?? '<all_locales>';
        $sql = <<<SQL
            UPDATE pim_catalog_product
            SET raw_values = JSON_SET(
               raw_values,
               "$.$attributeCode",
               JSON_OBJECT("$channelReference", JSON_OBJECT("$localeReference", JSON_ARRAY($value))
           )
            WHERE identifier = :product_identifier
        SQL;

        $this->getConnection()->executeStatement($sql, [
            'product_identifier' => $productIdentifier,
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductIdentifier($productIdentifier);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createReferenceDataAttribute(string $attributeCode, string $referenceDataName, bool $isScopable, bool $isLocalizable): void
    {
        $sql = <<<SQL
            INSERT INTO pim_catalog_attribute (is_localizable, is_scopable, code, attribute_type, backend_type, properties, sort_order, is_required, is_unique, entity_type, created, updated)
            VALUES (:is_localizable, :is_scopable, :attribute_code, 'pim_reference_data_multiselect', 'reference_data_options', :properties, 0, 0, 0, 'Akeneo\Pim\Enrichment\Component\Product\Model\Product', NOW(), NOW())
        SQL;

        $this->getConnection()->executeStatement(
            $sql,
            [
                'is_scopable' => (int)$isScopable,
                'is_localizable' => (int)$isLocalizable,
                'attribute_code' => $attributeCode,
                'properties' => serialize([
                    'reference_data_name' => $referenceDataName,
                ]),
            ],
        );
    }

    private function attributeExists(string $attributeCode): bool
    {
        $connection = $this->get('database_connection');
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT *
                FROM pim_catalog_attribute
                WHERE code = :code
            ) AS is_existing
        SQL;

        $result = $connection->executeQuery($sql, ['code' => $attributeCode])->fetchColumn();

        return (bool) $result;
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
