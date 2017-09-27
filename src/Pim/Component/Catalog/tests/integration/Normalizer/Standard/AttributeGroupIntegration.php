<?php

namespace tests\integration\Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\tests\integration\Normalizer\Standard\AbstractStandardNormalizerTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupIntegration extends AbstractStandardNormalizerTestCase
{
    protected $purgeDatabaseForEachTest = false;

    public function testAttributeGroupWithoutAttribute()
    {
        $expected = [
            'code'       => 'other',
            'sort_order' => 100,
            'attributes' => [],
            'labels'     => [
                'en_US' => 'Other',
                'fr_FR' => 'Autre'
            ]
        ];

        $this->assert('other', $expected);
    }

    public function testAttributeGroupWithAttributes()
    {
        $expected = [
            'code'       => 'attributeGroupB',
            'sort_order' => 2,
            'attributes' => [
                'a_metric',
                'a_metric_without_decimal',
                'a_metric_negative',
                'a_number_float',
                'a_number_float_negative',
                'a_number_integer',
                'a_number_integer_negative',
                'a_simple_select',
                'a_localizable_image',
                'a_scopable_image',
                'a_localizable_scopable_image',
                'a_simple_select_color',
                'a_simple_select_size'
            ],
            'labels'     => [
                'en_US' => 'Attribute group B',
                'fr_FR' => 'Groupe d\'attribut B'
            ]
        ];

        $this->assert('attributeGroupB', $expected);
    }

    /**
     * @param string $identifier
     * @param array  $expected
     */
    private function assert($identifier, array $expected)
    {
        $repository = $this->get('pim_catalog.repository.attribute_group');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier($identifier), 'standard');

        $this->assertSame($expected, $result);
    }
}
