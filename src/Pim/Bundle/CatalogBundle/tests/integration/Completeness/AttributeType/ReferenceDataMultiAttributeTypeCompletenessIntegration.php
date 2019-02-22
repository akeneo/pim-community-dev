<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeTestCase;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for multi reference data attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataMultiAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteMultiSelectReferenceData()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_multi_select_reference_data',
            AttributeTypes::REFERENCE_DATA_MULTI_SELECT
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_multi_select_reference_data' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['zorbeez'],
                        ],
                    ],
                ],
            ]
        );


        $this->assertComplete($productComplete);
    }

    public function testNotCompleteMultiSelectReferenceData()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_multi_select_reference_data',
            AttributeTypes::REFERENCE_DATA_MULTI_SELECT
        );

//        TODO: This cannot work now, but will on TIP-613. Test is added now so it will not forgotten.
//        $productDataNull = $this->createProductWithStandardValues(
//            $family,
//            'product_data_null',
//            [
//                'values' => [
//                    'a_multi_select_reference_data' => [
//                        [
//                            'locale' => null,
//                            'scope'  => null,
//                            'data'   => null,
//                        ],
//                    ],
//                ],
//            ]
//        );
//        $this->assertNotComplete($productDataNull);

        $productDataEmptyArray = $this->createProductWithStandardValues(
            $family,
            'product_data_empty_array',
            [
                'values' => [
                    'a_multi_select_reference_data' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productDataEmptyArray);

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValues);
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

        $attribute->setReferenceDataName('fabrics');
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
