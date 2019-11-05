<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * When deleting a value from a product that is unique, it should delete the row from the unique value table.
 *
 * @see https://akeneo.atlassian.net/browse/PIM-8312
 */
class DeleteUniqueValueInDatabaseIntegration extends TestCase
{
    public function test_that_unique_value_is_deleted_in_database_when_values_is_deleted_in_product_raw_value()
    {
        $attributeId = $this->createAttributeWithUniqueConstraint('name');
        $this->createProductWithUniqueValue('name', 'my_unique_value');

        $isValueExistingInUniqueTable = $this
            ->get('database_connection')
            ->fetchColumn('SELECT EXISTS(SELECT * FROM pim_catalog_product_unique_data WHERE attribute_id = :attribute_id)', [
                'attribute_id' => $attributeId
            ], 0);

        Assert::assertTrue((bool) $isValueExistingInUniqueTable, 'Unique value should exist in database.');

        $this->deleteUniqueValueForAttribute('name');

        $isValueExistingInUniqueTable = $this
            ->get('database_connection')
            ->fetchColumn('SELECT EXISTS(SELECT * FROM pim_catalog_product_unique_data where attribute_id = :attribute_id)', [
                'attribute_id' => $attributeId
            ], 0);

        Assert::assertFalse((bool) $isValueExistingInUniqueTable, 'Unique value is not deleted in pim_catalog_product_unique_data when deleting a product value.');
    }

    public function test_that_unique_value_is_deleted_in_database_when_values_are_set_at_null()
    {
        $attributeId = $this->createAttributeWithUniqueConstraint('name');
        $this->createProductWithUniqueValue('name', 'my_unique_value');

        $isValueExistingInUniqueTable = $this
            ->get('database_connection')
            ->fetchColumn('SELECT EXISTS(SELECT * FROM pim_catalog_product_unique_data WHERE attribute_id = :attribute_id)', [
                'attribute_id' => $attributeId
            ], 0);

        Assert::assertTrue((bool) $isValueExistingInUniqueTable, 'Unique value should exist in database.');

        $this->setUniqueValueAtNullForAttribute('name');

        $isValueExistingInUniqueTable = $this
            ->get('database_connection')
            ->fetchColumn('SELECT EXISTS(SELECT * FROM pim_catalog_product_unique_data where attribute_id = :attribute_id)', [
                'attribute_id' => $attributeId
            ], 0);

        Assert::assertFalse((bool) $isValueExistingInUniqueTable, 'Unique value is not deleted in pim_catalog_product_unique_data when deleting a product value.');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog('minimal');
    }

    private function createAttributeWithUniqueConstraint(string $string): int
    {
        $attribute = $this->getAttributeBuilder()->build([
            'code' => 'name',
            'type' => AttributeTypes::TEXT,
            'unique' => true,
            'group' => 'other'
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute->getId();
    }

    private function createProductWithUniqueValue(string $attributeCode, string $uniqueValueData): void
    {
        $product = $this->getProductBuilder()->withIdentifier('foo')->withValue($attributeCode, $uniqueValueData)->build();
        $this->get('pim_catalog.saver.product')->save($product);

        // We have the following flow:
        //      1. create a product "foo". Currently, it has no ID as it has not been saved yet to database.
        //      2. validate this product
        //              pim_catalog.validator.unique_value_set is called and foo is assigned to a spl_object_hash (see https://github.com/akeneo/pim-community-dev/blob/master/src/Akeneo/Pim/Enrichment/Component/Product/Validator/UniqueValuesSet.php#L86)
        //      3. save this project to database, "foo" now has the ID 1
        //      3. update this product
        //      4. validate this product again
        //              foo is assigned to a spl_object_hash while our product now has an ID... the "value" foo
        //              is considered as already existing
        //      BOOOM
        // so, that's why we need to reset the unique value set...
        // TODO: ideally, after each unitary or bulk product save, this set should be reset
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    private function setUniqueValueAtNullForAttribute(string $attributeCode): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $product->getValues()->removeByAttributeCode($attributeCode);
        $product->getValues()->add(ScalarValue::value('name', null));

        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $constraintList->count());

        $this->get('pim_catalog.saver.product')->save($product);
    }


    private function deleteUniqueValueForAttribute(string $attributeCode): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $product->getValues()->removeByAttributeCode($attributeCode);
        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $constraintList->count());

        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function getAttributeBuilder(): EntityBuilder
    {
        return $this->get('akeneo_integration_tests.base.attribute.builder');
    }

    private function getProductBuilder(): Builder\Product
    {
        return $this->get('akeneo_integration_tests.catalog.product.builder');
    }
}
