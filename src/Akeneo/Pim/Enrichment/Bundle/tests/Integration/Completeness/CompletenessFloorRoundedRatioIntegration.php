<?php


namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Pim\Component\Catalog\AttributeTypes;

class CompletenessFloorRoundedRatioIntegration extends AbstractCompletenessTestCase
{
    public function testFloorRoundedRatio()
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $extraAttribute = $this->createAttribute('a_number', AttributeTypes::NUMBER);

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT
        );

        $requirement = $this->get('pim_catalog.factory.attribute_requirement')
            ->createAttributeRequirement($extraAttribute, $channel, true);
        $family->addAttributeRequirement($requirement);

        $this->get('pim_catalog.saver.family')->save($family);

        $firstProduct = $this->createProductWithStandardValues(
            $family,
            'one_third_of_the_attributes_filled_in',
            [
                'values' => [
                    'a_text'   => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                    'a_number' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );

        // the real ratio would 1/3 = 33.33333...%
        // here we want to floor to 33%
        $this->assertEquals(33, $firstProduct->getCompletenesses()->first()->getRatio());

        $secondProduct = $this->createProductWithStandardValues(
            $family,
            'two_third_of_the_attributes_filled_in',
            [
                'values' => [
                    'a_text'   => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Just a text.',
                        ],
                    ],
                    'a_number' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );
        // the real ratio would 123 = 66.666666...%
        // here we want to floor to 66%
        $this->assertEquals(66, $secondProduct->getCompletenesses()->first()->getRatio());
    }
}
