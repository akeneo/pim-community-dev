<?php
namespace Oro\Bundle\NotificationBundle\Tests\Unit;

use Oro\Bundle\NotificationBundle\OroNotificationBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class OroNavigationBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');

        $container->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'))
            ->will($this->returnSelf());

        $container->expects($this->at(1))
            ->method('addCompilerPass')
            ->with(
                $this->isInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'),
                $this->equalTo(PassConfig::TYPE_AFTER_REMOVING)
            )
            ->will($this->returnSelf());

        $bundle = new OroNotificationBundle($kernel);
        $bundle->build($container);
    }
}
