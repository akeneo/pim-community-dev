<?php
namespace Oro\Bundle\NotificationBundle\Tests\Unit;

use Oro\Bundle\NotificationBundle\OroNotificationBundle;

class OroNavigationBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $bundle = new OroNotificationBundle();
        $bundle->build($container);
    }
}
