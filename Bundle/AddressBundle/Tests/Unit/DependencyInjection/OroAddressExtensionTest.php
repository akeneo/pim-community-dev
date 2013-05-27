<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\AddressBundle\DependencyInjection\OroAddressExtension;

class OroAddressExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $extension = new OroAddressExtension();
        $configs = array();
        $isCalled = false;
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->any())
            ->method('setParameter')
            ->will(
                $this->returnCallback(
                    function ($name, $value) use (&$isCalled) {
                        if ($name == 'oro_address' && is_array($value)) {
                            $isCalled = true;
                        }
                    }
                )
            );

        $extension->load($configs, $container);

        $this->assertTrue($isCalled);
    }
}
