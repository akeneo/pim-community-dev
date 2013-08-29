<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Pim\Bundle\ProductBundle\EventListener\UpdateCompletenessListener;
use Pim\Bundle\ProductBundle\Entity\Channel;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\AttributeRequirement;
use Pim\Bundle\ProductBundle\Entity\Locale;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCompletenessListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $listener = new UpdateCompletenessListener();
        $this->assertEquals($listener->getSubscribedEvents(), array('postPersist', 'postUpdate', 'onFlush', 'postFlush'));
    }

    /**
     * Test related method
     */
    public function testPostPersist()
    {
        $listener = new UpdateCompletenessListener();
        $this->assertFalse($listener->hasChanged());
        $mock = $this->getPostPersistMock();
        $listener->postPersist($mock);
        $this->assertTrue($listener->hasChanged());
    }

    /**
     * Test related method
     */
    public function testPostUpdate()
    {
        $listener = new UpdateCompletenessListener();
        $this->assertFalse($listener->hasChanged());
        $mock = $this->getPostUpdateMock();
        $listener->postUpdate($mock);
        $this->assertTrue($listener->hasChanged());
    }

    /**
     * Test related method
     */
    public function testPostFlush()
    {
        $listener = new UpdateCompletenessListener();
        $mock = $this->getPostPersistMock();
        $listener->postPersist($mock);
        $mock = $this->getPostUpdateMock();
        $listener->postUpdate($mock);

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->any())
            ->method('getPendingRepositoryMock')
            ->will($this->returnValue($this->getPendingRepositoryMock()));

        $mock = $this
            ->getMockBuilder('Doctrine\ORM\Event\PostFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($emMock));

        $listener->postFlush($mock);
    }

    /**
     * @return Doctrine\ORM\Event\LifecycleEventArgs
     */
    protected function getPostPersistMock()
    {
        $channel = new Channel();
        $channel->setCode('mynewchan');
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($channel));

        return $mock;
    }

    /**
     * @return Doctrine\ORM\Event\LifecycleEventArgs
     */
    protected function getPostUpdateMock()
    {
        $family = new Family();
        $requirement = new AttributeRequirement();
        $requirement->setFamily($family);
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($requirement));

        return $mock;
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock()
    {
        $emMock = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->getPendingRepositoryMock()));

        return $emMock;
    }

    /**
     * @return Doctrine\ORM\UnitOfWork
     */
    protected function getUnitOfWorkMock()
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getPendingRepositoryMock()
    {
        $repo = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        return $repo;
    }
}
