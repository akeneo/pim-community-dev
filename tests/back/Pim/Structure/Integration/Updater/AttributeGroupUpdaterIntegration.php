<?php

namespace AkeneoTest\Pim\Structure\Integration\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeGroupUpdater;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;

class AttributeGroupUpdaterIntegration extends TestCase
{
    public function testUpdateObjectInAttributeGroupUpdater()
    {
        $this->expectException(InvalidObjectException::class);
        $this->expectExceptionMessage('Expects a "Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface", "stdClass" given.');

        $this->getUpdater()->update(new \stdClass(), []);
    }

    public function testAttributeGroupUpdateWithNullLabels()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "labels" expects an array as data, "NULL" given.');

        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['labels' => null]);
    }

    public function testAttributeGroupUpdateWithNullAttributes()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "attributes" expects an array as data, "NULL" given.');

        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['attributes' => null]);
    }

    public function testAttributeGroupUpdateWithNonScalarLabels()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('one of the "labels" values is not a scalar');

        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['labels' => ['en_US' => []]]);
    }

    public function testAttributeGroupUpdateWithNonScalarAttributess()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('one of the "attributes" values is not a scalar');

        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['attributes' => [[]]]);
    }

    public function testAttributeGroupUpdateWithNonScalarCode()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "code" expects a scalar as data, "array" given.');

        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['code' => []]);
    }

    public function testAttributeGroupUpdateWithNonScalarSortOrder()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "sort_order" expects a scalar as data, "array" given.');

        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['sort_order' => []]);
    }

    public function testAttributeGroupUpdateWithUnknownProperty()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Property "unknown_property" does not exist.');

        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['unknown_property' => null]);
    }

    public function testAttributeGroupUpdateWithNonExistingAttributes()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "attributes" expects a valid attribute code. The attribute does not exist, "unknown_attribute" given.');

        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['code' => 'attributeGroupA', 'attributes' => ['unknown_attribute']]);
    }

    public function testSuccessAttributeGroupUpdate()
    {
        $attributeGroup = $this->createAttributeGroup();
        $data = [
            'code'       => 'attributeGroupA',
            'sort_order' => 1,
            'attributes' => ['a_text'],
            'labels'     => [
                'fr_FR' => 'Groupe A',
                'en_US' => 'Group A',
            ],
        ];
        $this->getUpdater()->update(
            $attributeGroup,
            $data
        );

        $this->assertSame($data['code'], $attributeGroup->getCode());
        $this->assertSame($data['sort_order'], $attributeGroup->getSortOrder());
        $this->assertSame(current($data['attributes']), current($attributeGroup->getAttributes()->toArray())->getCode());
        $this->assertCount(1, $attributeGroup->getAttributes());
        $this->assertSame($data['labels']['fr_FR'], $attributeGroup->getTranslation('fr_FR')->getLabel());
        $this->assertSame($data['labels']['en_US'], $attributeGroup->getTranslation('en_US')->getLabel());
    }

    /**
     * @return AttributeGroupInterface
     */
    protected function createAttributeGroup()
    {
        return $this->get('pim_catalog.factory.attribute_group')->create();
    }

    /**
     * @return AttributeGroupUpdater
     */
    protected function getUpdater()
    {
        return $this->get('pim_catalog.updater.attribute_group');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
