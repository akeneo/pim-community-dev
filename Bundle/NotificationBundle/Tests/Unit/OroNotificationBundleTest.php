<?php
namespace Oro\Bundle\NotificationBundle\Tests\Unit;

use Oro\Bundle\NotificationBundle\OroNotificationBundle;

class OroNavigationBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');

        $bundle = new OroNotificationBundle($kernel);
        $bundle->build($container);
    }
}
