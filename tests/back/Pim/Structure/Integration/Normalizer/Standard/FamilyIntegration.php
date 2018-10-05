<?php

namespace AkeneoTest\Pim\Structure\Integration\Normalizer\Standard;

use Akeneo\Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyIntegration extends TestCase
{
    public function testFamily()
    {
        $expected = [
            'code'                   => 'familyA',
            'attributes'             => [
                'a_date',
                'a_file',
                'a_localizable_image',
                'a_localized_and_scopable_text_area',
                'a_metric',
                'a_multi_select',
                'a_number_float',
                'a_number_float_negative',
                'a_number_integer',
                'a_price',
                'a_ref_data_multi_select',
                'a_ref_data_simple_select',
                'a_scopable_price',
                'a_simple_select',
                'a_text',
                'a_text_area',
                'a_yes_no',
                'an_image',
                'sku'
            ],
            'attribute_as_label'     => 'sku',
            'attribute_as_image'     => 'an_image',
            'attribute_requirements' => [
                'ecommerce' => [
                    'a_date',
                    'a_file',
                    'a_localizable_image',
                    'a_localized_and_scopable_text_area',
                    'a_metric',
                    'a_multi_select',
                    'a_number_float',
                    'a_number_float_negative',
                    'a_number_integer',
                    'a_price',
                    'a_ref_data_multi_select',
                    'a_ref_data_simple_select',
                    'a_scopable_price',
                    'a_simple_select',
                    'a_text',
                    'a_text_area',
                    'a_yes_no',
                    'an_image',
                    'sku'
                ],
                'ecommerce_china' => [
                    'sku'
                ],
                'tablet' => [
                    'a_date',
                    'a_file',
                    'a_localizable_image',
                    'a_localized_and_scopable_text_area',
                    'a_metric',
                    'a_multi_select',
                    'a_number_float',
                    'a_number_float_negative',
                    'a_number_integer',
                    'a_price',
                    'a_ref_data_multi_select',
                    'a_ref_data_simple_select',
                    'a_scopable_price',
                    'a_simple_select',
                    'a_text',
                    'a_text_area',
                    'a_yes_no',
                    'an_image',
                    'sku'
                ]
            ],
            'labels' => [
                'fr_FR' => 'Une famille A',
                'en_US' => 'A family A',
            ]
        ];

        $repository = $this->get('pim_catalog.repository.family');
        $serializer = $this->get('pim_standard_format_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('familyA'), 'standard');

        $this->assertSame($expected, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
