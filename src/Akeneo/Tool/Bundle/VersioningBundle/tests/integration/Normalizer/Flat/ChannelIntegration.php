<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Normalizer\Flat;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelIntegration extends AbstractFlatNormalizerTestCase
{
    public function testChannel()
    {
        $expected = [
            'code'        => 'tablet',
            'currencies'  => 'USD,EUR',
            'locales'     => 'de_DE,en_US,fr_FR',
            'label-en_US' => 'Tablet',
            'label-fr_FR' => 'Tablette',
            'category'    => 'master'
        ];

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('tablet');
        $flatAttribute = $this->get('pim_versioning.serializer')->normalize($channel, 'flat');

        $this->assertSame($expected, $flatAttribute);
    }
}
