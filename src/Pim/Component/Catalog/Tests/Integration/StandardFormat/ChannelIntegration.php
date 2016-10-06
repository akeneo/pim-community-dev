<?php

namespace Pim\Component\Catalog\Tests\Integration\StandardFormat;

use Pim\Integration\PimTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelIntegration extends PimTestCase
{
    public function testChannel()
    {
        $expected = [
            'code'             => 'tablet',
            'label'            => 'Tablet',
            'currencies'       => ['USD', 'EUR'],
            'locales'          => ['de_DE', 'en_US', 'fr_FR'],
            'category_tree'    => 'master',
            'conversion_units' => []
        ];

        $repository = $this->get('pim_catalog.repository.channel');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('tablet'), 'standard');

        $this->assertSame($expected, $result);
    }
}
