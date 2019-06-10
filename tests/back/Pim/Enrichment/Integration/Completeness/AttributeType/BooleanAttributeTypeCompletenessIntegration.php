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
    public function test_that_boolean_values_are_always_complete()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_boolean',
            AttributeTypes::BOOLEAN
        );

        $productWithTrue = $this->createProductWithStandardValues(
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
        $this->assertComplete($productWithTrue);

        $productWithFalse = $this->createProductWithStandardValues(
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
        $this->assertComplete($productWithFalse);

        $productWithNull = $this->createProductWithStandardValues(
            $family,
            'product_complete_null',
            [
                'values' => [
                    'a_boolean' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => null,
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productWithNull);
        $this->assertBooleanValueIsFalse($productWithNull, 'a_boolean');

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertComplete($productWithoutValues);
        $this->assertBooleanValueIsFalse($productWithoutValues, 'a_boolean');
    }

    /**
     * For now, when creating an empty boolean product value, it is automatically
     * set to false by the product builder.
     *
     * @param ProductInterface $product
     * @param string           $attributeCode
     */
    private function assertBooleanValueIsFalse(ProductInterface $product, $attributeCode)
    {
        $booleanValue = $product->getValue($attributeCode, null, null);
        $this->assertFalse($booleanValue->getData());
    }
}
