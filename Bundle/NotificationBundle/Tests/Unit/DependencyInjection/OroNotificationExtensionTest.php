<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\NotificationBundle\DependencyInjection\OroNotificationExtension;

class OroAddressExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $extension = new OroNotificationExtension();
        $configs = array();
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->exactly(2))
            ->method('addResource');

        $extension->load($configs, $container);
    }
}
