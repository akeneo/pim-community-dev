<?php

namespace AkeneoTest\Pim\Structure\Integration\Remover;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;

class RemoveAttributeOptionIntegration extends TestCase
{
    public function testRemoveAnAttributeOptionFromNonScopableAndNonLocalizableSimpleSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(false, false, false, false);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromScopableAndNonLocalizableSimpleSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(true, false, false, false);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromNonScopableAndLocalizableSimpleSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(false, true, false, false);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromScopableAndLocalizableSimpleSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(true, true, false, false);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromLocalSpecificSimpleSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(false, true, true, false);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromNonScopableAndNonLocalizableMultiSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(false, false, false, true);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromScopableAndNonLocalizableMultiSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(true, false, false, true);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromNonScopableAndLocalizableMultiSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(false, true, false, true);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromScopableAndLocalizableMultiSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(true, true, false, true);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    public function testRemoveAnAttributeOptionFromLocalSpecificMultiSelectAttribute()
    {
        $attributeOption = $this->createAttributeOption(false, true, true, true);

        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);

        $dbContent = $this
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('attribute_code.option');

        $this->assertNull($dbContent);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param bool $isAttributeScopable
     * @param bool $isAttributeLocalizable
     * @param bool $isLocaleSpecific
     * @param bool $multi
     *
     * @return AttributeOptionInterface
     */
    private function createAttributeOption($isAttributeScopable, $isAttributeLocalizable, $isLocaleSpecific, $multi)
    {
        $attribute = new Attribute();
        $attribute->setCode('attribute_code');
        $group = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('other');
        $attribute->setGroup($group);
        $attribute->setEntityType(Product::class);
        $attribute->setAttributeType(
            $multi ? AttributeTypes::OPTION_MULTI_SELECT : AttributeTypes::OPTION_SIMPLE_SELECT
        );
        $attribute->setBackendType(
            $multi ? AttributeTypes::BACKEND_TYPE_OPTIONS : AttributeTypes::BACKEND_TYPE_OPTION
        );
        $attribute->setScopable($isAttributeScopable);
        $attribute->setLocalizable($isAttributeLocalizable);

        if ($isLocaleSpecific) {
            $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('en_US');
            $attribute->addAvailableLocale($locale);
        }

        $errors = $this->get('validator')->validate($attribute);
        $this->assertSame(0, $errors->count());

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $attributeOption = new AttributeOption();
        $attributeOption->setCode('option');
        $attributeOption->setAttribute($attribute);

        $errors = $this->get('validator')->validate($attributeOption);
        $this->assertSame(0, $errors->count());

        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);

        return $attributeOption;
    }
}
