<?php

namespace tests\integration\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupIntegration extends TestCase
{
    public function testGroup()
    {
        $expected = [
            'code'   => 'groupA',
            'type'   => 'RELATED',
        ];

        $group = $this->get('pim_catalog.repository.group')->findOneByIdentifier('groupA');
        $flatAttribute = $this->get('pim_versioning.serializer')->normalize($group, 'flat');

        $this->assertSame($expected, $flatAttribute);
    }
}
