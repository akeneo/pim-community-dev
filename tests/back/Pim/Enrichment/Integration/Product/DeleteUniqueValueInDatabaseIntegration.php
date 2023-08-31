<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Common\EntityBuilder;
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
        $this->createProductWithUniqueValue([new SetTextValue('name', null, null, 'my_unique_value')]);

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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
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

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProductWithUniqueValue(array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'foo',
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
    }

    private function deleteUniqueValueForAttribute(string $attributeCode): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $uniqueValue = $product->getValue($attributeCode);
        if (null !== $uniqueValue) {
            $product->removeValue($uniqueValue);
        }
        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $constraintList->count());

        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function getAttributeBuilder(): EntityBuilder
    {
        return $this->get('akeneo_integration_tests.base.attribute.builder');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
    }
}
