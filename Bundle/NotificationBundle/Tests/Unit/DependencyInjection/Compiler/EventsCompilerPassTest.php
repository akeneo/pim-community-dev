<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\NotificationBundle\DependencyInjection\Compiler\EventsCompilerPass;

class EventsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    const EVENT_NAME = 'test';

    public function testCompile()
    {
        $container  = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $dispatcher = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $repository = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Entity\Repository\EventRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_notification.manager')
            ->will($this->returnValue(true));

        $container->expects($this->once())
            ->method('getDefinition')
            ->with('event_dispatcher')
            ->will($this->returnValue($dispatcher));

        $container->expects($this->once())
            ->method('get')
            ->with('doctrine.orm.entity_manager')
            ->will($this->returnValue($em));

        $em->expects($this->once())
            ->method('getRepository')
            ->with('Oro\Bundle\NotificationBundle\Entity\Event')
            ->will($this->returnValue($repository));

        $repository->expects($this->once())
            ->method('getEventNames')
            ->will($this->returnValue(array(array('name' => self::EVENT_NAME))));

        $dispatcher->expects($this->once())
            ->method('addMethodCall')
            ->with(
                'addListenerService',
                array(self::EVENT_NAME, array('oro_notification.manager', 'process'))
            );

        $compiler = new EventsCompilerPass();
        $compiler->process($container);
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

        $compiler = new EventsCompilerPass();
        $compiler->process($container);
    }
}
