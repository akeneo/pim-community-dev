<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\NavigationBundle\DependencyInjection\Compiler\MenuBuilderChainPass;
use Symfony\Component\DependencyInjection\Reference;

class MenuBuilderPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessSkip()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();
        $containerMock->expects($this->exactly(2))
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_menu.builder_chain'),
                    $this->equalTo('oro_navigation.item.factory')
                )
            )
            ->will($this->returnValue(false));
        $containerMock->expects($this->never())
            ->method('getDefinition');
        $containerMock->expects($this->never())
            ->method('findTaggedServiceIds');

        $compilerPass = new MenuBuilderChainPass();
        $compilerPass->process($containerMock);
    }

    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $definition->expects($this->exactly(4))
            ->method('addMethodCall');
        $definition->expects($this->at(0))
            ->method('addMethodCall')
            ->with('addBuilder', array(new Reference('service1'), 'test'));
        $definition->expects($this->at(1))
            ->method('addMethodCall')
            ->with('addBuilder', array(new Reference('service2'), 'test'));
        $definition->expects($this->at(3))
            ->method('addMethodCall')
            ->with('addBuilder', array(new Reference('service1')));
        $definition->expects($this->at(5))
            ->method('addMethodCall')
            ->with('addBuilder', array(new Reference('service2')));

        $serviceIds = array(
            'service1' => array(array('alias' => 'test')),
            'service2' => array(array('alias' => 'test'))
        );

        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->exactly(2))
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_menu.builder_chain'),
                    $this->equalTo('oro_navigation.item.factory')
                )
            )
            ->will($this->returnValue(true));

        $containerMock->expects($this->exactly(4))
            ->method('getDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_menu.builder_chain'),
                    $this->equalTo('oro_navigation.item.factory'),
                    $this->equalTo('service1'),
                    $this->equalTo('service2')
                )
            )
            ->will($this->returnValue($definition));

        $containerMock->expects($this->exactly(2))
            ->method('findTaggedServiceIds')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_menu.builder'),
                    $this->equalTo('oro_navigation.item.builder')
                )
            )
            ->will($this->returnValue($serviceIds));

        $compilerPass = new MenuBuilderChainPass();
        $compilerPass->process($containerMock);
    }
}
