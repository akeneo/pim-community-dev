<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for image attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImageAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteImage()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'an_image',
            AttributeTypes::IMAGE
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'an_image' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')),
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productComplete);
    }

    public function testNotCompleteImage()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'an_image',
            AttributeTypes::IMAGE
        );

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_empty',
            [
                'values' => [
                    'an_image' => [
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
        $this->assertMissingAttributeForProduct($productDataNull, ['an_image']);

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValues);
        $this->assertMissingAttributeForProduct($productWithoutValues, ['an_image']);
    }
}
