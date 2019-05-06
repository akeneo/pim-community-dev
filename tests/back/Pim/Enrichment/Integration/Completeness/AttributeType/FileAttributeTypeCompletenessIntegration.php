<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for file attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FileAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteFile()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_file',
            AttributeTypes::FILE
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_file' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => $this->getFileInfoKey($this->getFixturePath('akeneo.txt')),
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productComplete);
    }

    public function testNotCompleteFile()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_file',
            AttributeTypes::FILE
        );

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_data_null',
            [
                'values' => [
                    'a_file' => [
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
        $this->assertMissingAttributeForProduct($productDataNull, ['a_file']);

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValues);
        $this->assertMissingAttributeForProduct($productWithoutValues, ['a_file']);
    }
}
