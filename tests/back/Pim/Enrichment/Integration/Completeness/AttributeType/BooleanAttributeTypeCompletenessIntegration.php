<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness\AttributeType;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for boolean attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BooleanAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteBoolean()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_boolean',
            AttributeTypes::BOOLEAN
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete_true',
            [
                'values' => [
                    'a_boolean' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => true,
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productComplete);

        $productCompleteFalse = $this->createProductWithStandardValues(
            $family,
            'product_complete_false',
            [
                'values' => [
                    'a_boolean' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => false,
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productCompleteFalse);
    }

    public function testNotCompleteBoolean()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_boolean',
            AttributeTypes::BOOLEAN
        );

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_data_null',
            [
                'values' => [
                    'a_boolean' => [
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

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValues);
    }
}
