<?php

namespace tests\integration\Pim\Component\Catalog\Standard;

use Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupIntegration extends TestCase
{
    public function testVariantGroup()
    {
        $expected = [
            'code'   => 'variantA',
            'type'   => 'VARIANT',
            'axes'   => ['a_simple_select'],
            'values' => [
                'a_text' => [
                    ['locale' => null, 'scope' => null, 'data' => 'A name']
                ]
            ],
            'labels' => []
        ];

        $repository = $this->get('pim_catalog.repository.group');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('variantA'), 'standard');

        $this->assertSame($expected, $result);
    }
}
