<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\ImapBundle\EventListener\ImapConfigurationSaveListener;

class ImapConfigurationSaveListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImapConfigurationSaveListener | \PHPUnit_Framework_MockObject_MockObject */
    protected $subscriber;

    public function setUp()
    {
        $this->subscriber = $this->getMock(
            'Oro\Bundle\ImapBundle\EventListener\ImapConfigurationSaveListener',
            array('getConfigurationChangeSet')
        );
    }

    public function tearDown()
    {
        unset($this->subscriber);
    }

    /**
     * Test that listener subscribed to needed events
     */
    public function testGetSubscribedEvents()
    {
        $result = $this->subscriber->getSubscribedEvents();

        $this->assertInternalType('array', $result);
        $this->assertContains('onFlush', $result);
    }

    /**
     * Test onFlush logic
     *
     * @dataProvider entitiesProvider
     */
    public function testOnFlushBadScenario($scheduledEntityUpdates, $expectCompute)
    {
        $em  = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')->disableOriginalConstructor()->getMock();

        $em->expects($this->once())->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $event = new OnFlushEventArgs($em);

        $uow->expects($this->once())->method('getScheduledEntityUpdates')
            ->will($this->returnValue($scheduledEntityUpdates));
        $this->subscriber->expects($this->exactly((int)$expectCompute))->method('getConfigurationChangeSet');

        $this->subscriber->onFlush($event);
    }

    /**
     * @return array
     */
    public function entitiesProvider()
    {
        return array(
            'empty updated array' => array(
                array(),
                false
            ),
            'not needed entity'   => array(
                array(new \stdClass()),
                false
            ),
            'correct entity'      => array(
                array($this->getMockForAbstractClass('Oro\Bundle\ImapBundle\Entity\ImapConfigurationOwnerInterface')),
                false
            )
        );
    }

    /**
     * Test onFlush logic
     * full correct scenario
     */
    public function testOnFlush()
    {
        $em  = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->setMethods(array('refresh', 'persist', 'getUnitOfWork'))->disableOriginalConstructor()->getMock();
        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')->disableOriginalConstructor()->getMock();

        $imapConfiguration = new ImapEmailOrigin();

        $entity = $this->getMockForAbstractClass('Oro\Bundle\ImapBundle\Entity\ImapConfigurationOwnerInterface');
        $entity->expects($this->any())->method('getImapConfiguration')
            ->will($this->returnValue($imapConfiguration));

        $em->expects($this->once())->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $event = new OnFlushEventArgs($em);

        $uow->expects($this->once())->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array($entity)));

        $this->subscriber->expects($this->once())->method('getConfigurationChangeSet')
            ->will($this->returnValue(array('host' => 'someValue')));

        $phpUnit = $this;
        $em->expects($this->at(1))->method('refresh')->with($imapConfiguration);
        $em->expects($this->at(2))->method('persist')->with($imapConfiguration)->will(
            $this->returnCallback(
                // check that old configuration not active
                function ($imap) use ($phpUnit) {
                    $phpUnit->assertFalse($imap->getIsActive());
                }
            )
        );
        $em->expects($this->at(3))->method('persist');

        $entity->expects($this->once())->method('setImapConfiguration')->will(
            // check that newly created configuration assigned to target entity
            $this->returnCallback($this->getNotTheSameCallback($imapConfiguration))
        );

        $uow->expects($this->once())->method('computeChangeSets');

        $this->subscriber->onFlush($event);
    }

    /**
     * @param $object
     *
     * @return callable
     */
    protected function getNotTheSameCallback($object)
    {
        $phpUnit = $this;

        return function ($imap) use ($object, $phpUnit) {
            $phpUnit->assertNotSame($imap, $object);
        };
    }

    /**
     * @dataProvider changeSetProvider
     *
     * @param      $changes
     * @param bool $shouldCollectConfigurationChangeSet
     *
     * @return mixed
     */
    public function testGetConfigurationChangeSet($changes, $shouldCollectConfigurationChangeSet = false)
    {
        $uow               = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')->disableOriginalConstructor()->getMock();
        $imapConfiguration = new ImapEmailOrigin();

        $entity = $this->getMockForAbstractClass('Oro\Bundle\ImapBundle\Entity\ImapConfigurationOwnerInterface');
        $entity->expects($this->any())->method('getImapConfiguration')
            ->will($this->returnValue($imapConfiguration));

        $uow->expects($this->at(0))->method('getEntityChangeSet')
            ->will($this->returnValue($changes));

        if ($shouldCollectConfigurationChangeSet) {
            $uow->expects($this->at(1))->method('getEntityChangeSet');
        }

        $subscriber = new ImapConfigurationSaveListener();
        $reflection = new \ReflectionMethod($subscriber, 'getConfigurationChangeSet');
        $reflection->setAccessible(true);

        return $reflection->invoke($subscriber, $uow, $entity);
    }

    /**
     * @return array
     */
    public function changeSetProvider()
    {
        return array(
            'added new entity, should return empty changeset' => array(
                array(
                    'imapConfiguration' => array(null, 1)
                )
            ),
            'changeset contains the same id that means configuration was changed' => array(
                array(
                    'imapConfiguration' => array(1, 1),
                    true
                )
            ),

        );
    }
}
