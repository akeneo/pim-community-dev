<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\NotificationBundle\DependencyInjection\Compiler\NotificationHandlerPass;

class NotificationHandlerPassTest extends \PHPUnit_Framework_TestCase
{
    const TEST_SERVICE_ID = 'test.id';

    /**
     * @var NotificationHandlerPass
     */
    private $compiler;

    public function setUp()
    {
        $this->compiler = new NotificationHandlerPass();
    }

    public function tearDown()
    {
        unset($this->compiler);
    }

    public function testCompile()
    {
        $container  = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_notification.manager')
            ->will($this->returnValue(true));

        $container->expects($this->once())
            ->method('getDefinition')
            ->with('oro_notification.manager')
            ->will($this->returnValue($definition));

        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('notification.handler')
            ->will($this->returnValue(array(self::TEST_SERVICE_ID => null)));

        $definition->expects($this->once())
            ->method('addMethodCall')
            ->with(
                'addHandler',
                array(new Reference(self::TEST_SERVICE_ID))
            );

        $this->compiler->process($container);
    }

    public function testCompileManagerNotDefined()
    {
        $container  = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_notification.manager')
            ->will($this->returnValue(false));

        $container->expects($this->never())
            ->method('getDefinition');

        $this->compiler->process($container);
    }
}
