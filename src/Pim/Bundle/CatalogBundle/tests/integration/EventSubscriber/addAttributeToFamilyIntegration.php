<?php

declare(strict_types=1);

namespace tests\integration\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Checks that adding an attribute to the attribute list of a family correctly updates all its family variants if the
 * attribute's property "unique value" is set to true.
 *
 * The attribute should be added in the product level attribute set.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class addAttributeToFamilyIntegration extends TestCase
{
    public function testAddingUniqueValueAttributeToFamilyUpdatesAllItsRelatedFamilyVariants()
    {
        $newUniqueAttribute = $this->createUniqueValueAttribute('new_unique_value_attribute');
        $this->addAttributeToFamily($newUniqueAttribute, 'shoes');
        $this->assertAttributeIsAtProductLevelForAllVariantFamiliesOfFamily($newUniqueAttribute, 'shoes');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * Creates a new attribute with "unique value" property set to true.
     */
    private function createUniqueValueAttribute(string $attributeCode): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::TEXT);
        $attribute->setUnique(true);
        $attribute->setCode($attributeCode);
        $this->get('validator')->validate($attribute);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    /**
     * @param AttributeInterface $newUniqueAttribute
     * @param string             $code
     */
    private function addAttributeToFamily(AttributeInterface $newUniqueAttribute, string $code)
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($code);
        $family->addAttribute($newUniqueAttribute);
        $this->get('validator')->validate($family);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $code
     */
    private function assertAttributeIsAtProductLevelForAllVariantFamiliesOfFamily(
        AttributeInterface $attribute,
        string $code
    ) {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($code);
        foreach ($family->getFamilyVariants() as $familyVariant) {
            $productLevelAttributeSet = $familyVariant->getVariantAttributeSet($familyVariant->getNumberOfLevel());
            $this->assertTrue($productLevelAttributeSet->hasAttribute($attribute));
        }
    }
}
