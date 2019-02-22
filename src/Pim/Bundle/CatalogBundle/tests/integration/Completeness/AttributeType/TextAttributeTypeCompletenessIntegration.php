<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeTestCase;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for text attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteText()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'foo bar',
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productComplete);
    }

    public function testNotCompleteText()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT
        );

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_data_null',
            [
                'values' => [
                    'a_text' => [
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

        $productDataEmptyString = $this->createProductWithStandardValues(
            $family,
            'product_data_empty_string',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '',
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productDataEmptyString);

        $productWithoutValue = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValue);
    }
}
