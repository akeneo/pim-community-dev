<?php

namespace Pim\Component\Catalog\tests\integration\Updater;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Updater\AttributeGroupUpdater;

class AttributeGroupUpdaterIntegration extends TestCase
{
    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidObjectException
     * @expectedExceptionMessage Expects a "Pim\Component\Catalog\Model\AttributeGroupInterface", "stdClass" given.
     */
    public function testUpdateObjectInAttributeGroupUpdater()
    {
        $this->getUpdater()->update(new \stdClass(), []);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "labels" expects an array as data, "NULL" given.
     */
    public function testAttributeGroupUpdateWithNullLabels()
    {
        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['labels' => null]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "attributes" expects an array as data, "NULL" given.
     */
    public function testAttributeGroupUpdateWithNullAttributes()
    {
        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['attributes' => null]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage one of the "labels" values is not a scalar
     */
    public function testAttributeGroupUpdateWithNonScalarLabels()
    {
        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['labels' => ['en_US' => []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage one of the "attributes" values is not a scalar
     */
    public function testAttributeGroupUpdateWithNonScalarAttributess()
    {
        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['attributes' => [[]]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "code" expects a scalar as data, "array" given.
     */
    public function testAttributeGroupUpdateWithNonScalarCode()
    {
        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['code' => []]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "sort_order" expects a scalar as data, "array" given.
     */
    public function testAttributeGroupUpdateWithNonScalarSortOrder()
    {
        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['sort_order' => []]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "unknown_property" does not exist.
     */
    public function testAttributeGroupUpdateWithUnknownProperty()
    {
        $attributeGroup = $this->createAttributeGroup();

        $this->getUpdater()->update($attributeGroup, ['unknown_property' => null]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "attributes" expects a valid attribute code. The attribute does not exist, "unknown_attribute" given.
     */
    public function testAttributeGroupUpdateWithNonExistingAttributes()
    {
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
        return new Configuration( [Configuration::getTechnicalCatalogPath()]);
    }
}
