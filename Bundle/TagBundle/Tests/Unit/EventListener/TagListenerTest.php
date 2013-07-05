<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\EventListener;

use Oro\Bundle\TagBundle\EventListener\TagListener;

class TagListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TagListener
     */
    private $listener;

    /**
     * @var \Oro\Bundle\TagBundle\Entity\Taggable
     */
    private $resource;

    public function setUp()
    {
        $this->resource = $this->getMock('Oro\Bundle\TagBundle\Entity\Taggable');
    }

    /**
     * Test pre-remove doctrine listener
     */
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

    /**
     * Test that event listener bound to correct event
     */
    public function testSubscribedEvents()
    {
        $this->listener = new TagListener();

        $this->assertEquals($this->listener->getSubscribedEvents(), array('preRemove'));
    }
}
