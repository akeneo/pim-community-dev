<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for number attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NumberAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteNumber()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_number_integer',
            AttributeTypes::NUMBER
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_number_integer' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 42,
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productComplete);

        $productCompleteWithZero = $this->createProductWithStandardValues(
            $family,
            'product_complete_with_zero',
            [
                'values' => [
                    'a_number_integer' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 0,
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productCompleteWithZero);
    }

    public function testNotCompleteNumber()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_number_integer',
            AttributeTypes::NUMBER
        );

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_data_null',
            [
                'values' => [
                    'a_number_integer' => [
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
        $this->assertMissingAttributeForProduct($productDataNull, ['a_number_integer']);

        $productWithoutValue = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValue);
        $this->assertMissingAttributeForProduct($productWithoutValue, ['a_number_integer']);
    }
}
