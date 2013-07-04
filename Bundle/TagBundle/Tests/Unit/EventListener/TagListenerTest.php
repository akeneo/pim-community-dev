<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\EventListener;

use Oro\Bundle\TagBundle\EventListener\TagListener;

class TagListenerTest extends \PHPUnit_Framework_TestCase
{
    private $listener;

    private $resource;

    public function setUp()
    {
        $this->resource = $this->getMock('DoctrineExtensions\Taggable\Taggable');
    }

    public function testPreRemove()
    {
        $manager = $this->getMockBuilder('FPN\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())
            ->method('deleteTagging')
            ->with($this->resource);

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('fpn_tag.tag_manager'))
            ->will($this->returnValue($manager));

        $this->listener = new TagListener();
        $this->listener->setContainer($container);

        $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $args->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->resource));

        $this->listener->preRemove($args);
    }

    public function testSubscribedEvents()
    {
        $this->listener = new TagListener();

        $this->assertEquals($this->listener->getSubscribedEvents(), array('preRemove'));
    }
}
