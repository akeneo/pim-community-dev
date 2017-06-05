<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for simple reference data attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataSimpleAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testCompleteSimpleSelectReferenceData()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_simple_select_reference_data',
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_simple_select_reference_data' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'zomp',
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productComplete);
    }

    public function testNotCompleteSimpleSelectReferenceData()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_simple_select_reference_data',
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        );

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_data_null',
            [
                'values' => [
                    'a_simple_select_reference_data' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productDataNull);
        $this->assertMissingAttributeForProduct($productDataNull, ['a_simple_select_reference_data']);

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValues);
        $this->assertMissingAttributeForProduct($productWithoutValues, ['a_simple_select_reference_data']);
    }

    /**
     * {@inheritdoc}
     */
    protected function createAttribute(
        $code,
        $type,
        $localisable = false,
        $scopable = false,
        array $localesSpecific = []
    ) {
        $attribute = parent::createAttribute($code, $type);

        $attribute->setReferenceDataName('color');
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([
            Configuration::getMinimalCatalogPath(),
            Configuration::getReferenceDataFixtures()
        ]);
    }
}
