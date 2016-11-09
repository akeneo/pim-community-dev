<?php

namespace tests\integration\Pim\Component\Catalog\Standard;

use Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelIntegration extends TestCase
{
    public function testChannel()
    {
        $expected = [
            'code'             => 'tablet',
            'currencies'       => ['USD', 'EUR'],
            'locales'          => ['de_DE', 'en_US', 'fr_FR'],
            'category_tree'    => 'master',
            'conversion_units' => [],
            'labels'           => [
                'en_US' => 'Tablet',
                'fr_FR' => 'Tablette'
            ]
        ];

        $repository = $this->get('pim_catalog.repository.channel');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('tablet'), 'standard');

        $this->assertSame($expected, $result);
    }
}
