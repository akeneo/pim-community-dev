<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Entity\RegionTranslation;

class RegionTranslationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRegion()
    {
        $region      = new Region('combinedCode');
        $translation = new RegionTranslation();

        // set country property
        $reflection = new \ReflectionProperty('Oro\Bundle\AddressBundle\Entity\RegionTranslation', 'region');
        $reflection->setAccessible(true);
        $reflection->setValue($translation, $region);

        $this->assertEquals($region, $translation->getRegion());
    }
}
