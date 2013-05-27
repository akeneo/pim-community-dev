<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\AddressBundle\DependencyInjection\Compiler\AddressProviderPass;
use Symfony\Component\DependencyInjection\Reference;

class AddressProviderPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * Environment setup
     */
    public function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->getMock();
    }

    public function testProcessNotRegisterProvider()
    {
        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('oro_address.address.provider'))
            ->will($this->returnValue(false));

        $this->container->expects($this->never())
            ->method('getDefinition');
        $this->container->expects($this->never())
            ->method('findTaggedServiceIds');

        $compilerPass = new AddressProviderPass();
        $compilerPass->process($this->container);
    }

    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $definition->expects($this->at(0))
            ->method('addMethodCall')
            ->with($this->equalTo('addStorage'), $this->equalTo(array(new Reference('service1'), 'test')));
        $definition->expects($this->at(1))
            ->method('addMethodCall')
            ->with($this->equalTo('addStorage'), $this->equalTo(array(new Reference('service2'))));

        $serviceIds = array(
            'service1' => array(array('alias' => 'test')),
            'service2' => array(array('alias' => ''))
        );

        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('oro_address.address.provider'))
            ->will($this->returnValue(true));

        $this->container->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('oro_address.address.provider'))
            ->will($this->returnValue($definition));
        $this->container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('oro_address.storage'))
            ->will($this->returnValue($serviceIds));

        $compilerPass = new AddressProviderPass();
        $compilerPass->process($this->container);
    }
}
