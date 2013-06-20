<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\CountryTranslation;

class CountryTranslationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCountry()
    {
        $country     = new Country('iso2Code');
        $translation = new CountryTranslation();

        // set country property
        $reflection = new \ReflectionProperty('Oro\Bundle\AddressBundle\Entity\CountryTranslation', 'country');
        $reflection->setAccessible(true);
        $reflection->setValue($translation, $country);

        $this->assertEquals($country, $translation->getCountry());
    }
}
