<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class CheckAttributeIsNotMainIdentifierOnDeletionSubscriberIntegration extends TestCase
{
    public function test_it_throws_an_exception_on_remove_when_the_attribute_is_an_identifier(): void
    {
        $attributeIdentifier = $this->get('pim_catalog.repository.attribute')->getIdentifier();

        Assert::assertEquals(true, $attributeIdentifier->isMainIdentifier());

        $this->expectException(CannotRemoveAttributeException::class);
        $this->get('pim_catalog.remover.attribute')->remove($attributeIdentifier);
    }

    public function test_it_allows_a_non_main_identifier_attribute_to_be_removed(): void
    {
        $attribute = $this->createAttribute([
            'code' => 'ean',
            'type' => 'pim_catalog_identifier',
            'group' => 'other',
            'useable_as_grid_filter' => true
        ]);
        Assert::assertEquals(false, $attribute->isMainIdentifier());

        $this->get('pim_catalog.remover.attribute')->remove($attribute);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function createAttribute(array $data = []) : AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraintList = $this->get('validator')->validate($attribute);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }
}
