<?php

namespace tests\integration\Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\tests\integration\Normalizer\Standard\AbstractStandardNormalizerTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionIntegration extends AbstractStandardNormalizerTestCase
{
    public function testAttributeOption()
    {
        $expected = [
            'code'       => 'optionA',
            'attribute'  => 'a_multi_select',
            'sort_order' => 10,
            'labels'     => [
                'en_US' => 'Option A'
            ]
        ];

        $repository = $this->get('pim_catalog.repository.attribute_option');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('a_multi_select.optionA'), 'standard');

        $this->assertSame($expected, $result);
    }
}
