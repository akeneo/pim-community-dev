<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for boolean attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BooleanAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
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
        $this->assertMissingAttributeForProduct($productDataNull, ['a_boolean']);

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        // TODO: This is not as it should be, but inevitable because of PIM-6056
        // TODO: When PIM-6056 is fixed, we should be able to use "assertNotComplete"
        $this->assertComplete($productWithoutValues);
        $this->assertBooleanValueIsFalse($productWithoutValues, 'a_boolean');
    }

    /**
     * For now, when creating an empty boolean product value, it is automatically
     * set to false by the product builder.
     *
     * @todo To remove once PIM-6056 is fixed.
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
