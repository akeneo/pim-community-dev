<?php

namespace tests\integration\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleIntegration extends TestCase
{
    public function testLocale()
    {
        $expected = [
            'code' => 'en_US'
        ];

        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('en_US');
        $flatAttribute = $this->get('pim_versioning.serializer')->normalize($locale, 'flat');

        $this->assertSame($expected, $flatAttribute);
    }
}
