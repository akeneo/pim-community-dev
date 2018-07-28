<?php

namespace Akeneo\Tool\Component\Api\tests\integration\Normalizer;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeGroupIntegration extends AbstractNormalizerTestCase
{
    public function testAttributeGroup()
    {
        $expected = [
            'code'       => 'attributeGroupA',
            'sort_order' => 1,
            'attributes' => [
                'sku',
                'a_date',
                'a_file',
                'an_image',
                'a_price',
                'a_price_without_decimal',
                'a_ref_data_multi_select',
                'a_ref_data_simple_select',
                'a_text',
                'a_regexp',
                'a_text_area',
                'a_yes_no',
                'a_scopable_price',
                'a_localized_and_scopable_text_area',
            ],
            'labels'     => [
                'en_US' => 'Attribute group A',
                'fr_FR' => 'Groupe d\'attribut A',
            ],
        ];

        $repository = $this->get('pim_catalog.repository.attribute_group');
        $serializer = $this->get('pim_external_api_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('attributeGroupA'), 'external_api');

        $this->assertEquals($expected, $result);
    }
}
