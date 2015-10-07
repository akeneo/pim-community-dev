<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit;

use Oro\Bundle\NavigationBundle\OroNavigationBundle;

class OroNavigationBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->once())
            ->method('addCompilerPass')
            ->with(
                $this->isInstanceOf(
                    'Oro\Bundle\NavigationBundle\DependencyInjection\Compiler\MenuBuilderChainPass'
                )
            );

        $bundle = new OroNavigationBundle();
        $bundle->build($container);
    }
}
