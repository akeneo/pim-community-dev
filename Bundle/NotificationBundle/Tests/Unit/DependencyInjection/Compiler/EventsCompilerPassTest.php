<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\NotificationBundle\DependencyInjection\Compiler\EventsCompilerPass;

class EventsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    const EVENT_NAME = 'test';
    const CLASS_NAME = 'Oro\Bundle\NotificationBundle\Entity\Event';

    public function testCompile()
    {
        $container  = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $dispatcher = $this->getMock('Symfony\Component\DependencyInjection\Definition');

        $repository = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Entity\Repository\EventRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_notification.manager')
            ->will($this->returnValue(true));
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
            ->will($this->returnValue($this->configureEntityManagerMock($repository)));

        $container->expects($this->once())
            ->method('getParameter')
            ->with('oro_notification.event_entity.class')
            ->will($this->returnValue(self::CLASS_NAME));

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

    /**
     * Creates and configure EM mock object
     *
     * @param $repository
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function configureEntityManagerMock($repository)
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $schemaManager = $this->getMockBuilder('Doctrine\DBAL\Schema\MySqlSchemaManager')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $schemaManager->expects($this->once())
            ->method('listTableNames')
            ->will($this->returnValue(array('event_table_exist')));
        $connection->expects($this->once())
            ->method('getSchemaManager')
            ->will($this->returnValue($schemaManager));

        $metadata->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('event_table_exist'));

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue($metadata));

        $em->expects($this->once())
            ->method('getRepository')
            ->with('Oro\Bundle\NotificationBundle\Entity\Event')
            ->will($this->returnValue($repository));

        $em->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        return $em;
    }
}
