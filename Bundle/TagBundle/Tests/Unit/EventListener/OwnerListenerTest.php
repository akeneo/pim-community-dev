<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\EventListener;

use Oro\Bundle\TagBundle\EventListener\OwnerListener;

class OwnerListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OwnerListener
     */
    private $listener;

    /**
     * @var \Oro\Bundle\TagBundle\Entity\Taggable
     */
    private $resource;

    public function setUp()
    {
        $this->resource = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainUpdaterInterface');

        $this->user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

        $this->securityContext = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\SecurityContextInterface'
        );
        $token = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface'
        );
        $token->expects($this->exactly(2))
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $this->securityContext
            ->expects($this->exactly(4))
            ->method('getToken')
            ->will($this->returnValue($token));
    }

    /**
     * Test pre-remove doctrine listener
     */
    public function testPreUpdate()
    {
        $meta = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->equalTo($meta), $this->equalTo($this->resource));

        $manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));
        $manager->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($this->resource));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('security.context'))
            ->will($this->returnValue($this->securityContext));

        $this->resource->expects($this->once())
            ->method('setCreatedBy')
            ->with($this->user);

        $this->listener = new OwnerListener();
        $this->listener->setContainer($container);

        $args = $this->getMockBuilder('Doctrine\ORM\Event\PreUpdateEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $args->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->resource));

        $args->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($manager));

        $this->listener->preUpdate($args);
    }

    public function PrePersist()
    {
        $args = $this->getMockBuilder('Doctrine\ORM\Event\PreUpdateEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $args->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->resource));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('security.context'))
            ->will($this->returnValue($this->securityContext));

        $this->resource->expects($this->once())
            ->method('setCreatedBy')
            ->with($this->user);

        $this->listener = new OwnerListener();
        $this->listener->setContainer($container);
        $this->listener->prePersist($args);
    }
}
