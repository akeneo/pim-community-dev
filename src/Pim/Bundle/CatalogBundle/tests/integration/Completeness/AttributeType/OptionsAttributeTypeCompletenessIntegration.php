<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for multi select attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionsAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testCompleteOptions()
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('another_family');

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_multi_select' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['red_option', 'green_option'],
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productComplete);

        $productCompleteOneOption = $this->createProductWithStandardValues(
            $family,
            'product_complete_one_option',
            [
                'values' => [
                    'a_multi_select' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['blue_option'],
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productCompleteOneOption);
    }

    public function testNotCompleteOptions()
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('another_family');

        $productDataEmptyArray = $this->createProductWithStandardValues(
            $family,
            'product_data_empty_array',
            [
                'values' => [
                    'a_multi_select' => [
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
        $this->assertMissingAttributeForProduct($productDataEmptyArray, ['a_multi_select']);

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_data_null',
            [
                'values' => [
                    'a_multi_select' => [
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
        $this->assertMissingAttributeForProduct($productDataNull, ['a_multi_select']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_multi_select',
            AttributeTypes::OPTION_MULTI_SELECT
        );

        $aMultiSelect = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_multi_select');

        $redOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $redOption->setCode('red_option');
        $redOption->setAttribute($aMultiSelect);

        $greenOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $greenOption->setCode('green_option');
        $greenOption->setAttribute($aMultiSelect);

        $blueOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $blueOption->setCode('blue_option');
        $blueOption->setAttribute($aMultiSelect);

        $optionSaver = $this->get('pim_catalog.saver.attribute_option');
        $optionSaver->save($redOption);
        $optionSaver->save($greenOption);
        $optionSaver->save($blueOption);
    }
}
