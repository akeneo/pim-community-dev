<?php

namespace Akeneo\Tool\Component\Api\tests\integration\Normalizer;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyIntegration extends AbstractNormalizerTestCase
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
                'sku',
            ],
            'attribute_as_label'     => 'sku',
            'attribute_as_image'     => 'an_image',
            'attribute_requirements' => [
                'ecommerce'       => [
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
                    'sku',
                ],
                'ecommerce_china' => [
                    'sku',
                ],
                'tablet'          => [
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
                    'sku',
                ],
            ],
            'labels'                 => [
                'fr_FR' => 'Une famille A',
                'en_US' => 'A family A',
            ],
        ];

        $repository = $this->get('pim_catalog.repository.family');
        $serializer = $this->get('pim_external_api_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('familyA'), 'external_api');

        $this->assertEquals($expected, $result);
    }
}
