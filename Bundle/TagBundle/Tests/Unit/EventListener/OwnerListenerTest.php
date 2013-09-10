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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $securityContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    public function setUp()
    {
        $this->resource = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainUpdaterInterface');

        $this->user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

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
            ->expects($this->exactly(3))
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('security.context'))
            ->will($this->returnValue($this->securityContext));

        $this->listener = new OwnerListener();
        $this->listener->setContainer($this->container);
    }

    public function tearDown()
    {
        unset($this->listener);
        unset($this->resource);
        unset($this->securityContext);
        unset($this->user);
    }

    /**
     * Test pre-remove doctrine listener
     */
    public function testPreUpdate()
    {
        $meta = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->equalTo($meta), $this->equalTo($this->resource));

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));
        $manager->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($this->resource))
            ->will($this->returnValue($meta));

        $this->resource->expects($this->once())
            ->method('setUpdatedBy')
            ->with($this->user);

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

    public function testSkipNotNeededEntitiesOnPreUpdate()
    {
        $this->resource = $this->getMock('Oro\Bundle\TagBundle\Tests\Unit\Fixtures\Entity');
        $args = $this->getMockBuilder('Doctrine\ORM\Event\PreUpdateEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $args->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->resource));

        $this->resource->expects($this->never())
            ->method('setUpdatedBy');

        $this->listener->preUpdate($args);
    }

    public function testPrePersist()
    {
        $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $resource = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $args->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($resource));

        $resource->expects($this->once())
            ->method('setCreatedBy')
            ->with($this->user);

        $this->listener->prePersist($args);
    }

    public function testSkipNotNeededEntitiesOnPrePersist()
    {
        $this->resource = $this->getMock('Oro\Bundle\TagBundle\Tests\Unit\Fixtures\Entity');
        $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $args->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->resource));

        $this->resource->expects($this->never())
            ->method('setCreatedBy');

        $this->listener->prePersist($args);
    }
}
