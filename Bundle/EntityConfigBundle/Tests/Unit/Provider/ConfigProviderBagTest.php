<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderBag;

class ConfigProviderBagTest extends \PHPUnit_Framework_TestCase
{

    public function testBag()
    {
        $provider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->any())->method('getScope')->will($this->returnValue('testScope'));

        $providerBag = new ConfigProviderBag();

        $providerBag->addProvider($provider);

        $this->assertEquals(new ArrayCollection(array('testScope' => $provider)), $providerBag->getProviders());
        $this->assertEquals($provider, $providerBag->getProvider('testScope'));
        $this->assertTrue($providerBag->hasProvider('testScope'));
        $this->assertFalse($providerBag->hasProvider('wrongTestScope'));
    }
}
