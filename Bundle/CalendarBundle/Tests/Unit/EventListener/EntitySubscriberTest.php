<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarConnection;
use Oro\Bundle\CalendarBundle\EventListener\EntitySubscriber;
use Oro\Bundle\CalendarBundle\Notification\RemindTimeCalculator;
use Oro\Bundle\UserBundle\Entity\User;

class EntitySubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $uow;

    /** @var EntitySubscriber */
    protected $subscriber;

    protected function setUp()
    {
        $this->subscriber = new EntitySubscriber(new RemindTimeCalculator(15));
        $this->em         = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->uow        = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(
                //@codingStandardsIgnoreStart
                Events::prePersist,
                Events::preUpdate,
                Events::onFlush
                //@codingStandardsIgnoreEnd
            ),
            $this->subscriber->getSubscribedEvents()
        );
    }

    public function testPrePersistForEventWithReminder()
    {
        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new LifecycleEventArgs($event, $this->em);

        $startDate        = new \DateTime('now', new \DateTimeZone('UTC'));
        $expectedRemindAt = clone $startDate;
        $expectedRemindAt->sub(new \DateInterval('PT15M'));

        $event->expects($this->once())
            ->method('getReminder')
            ->will($this->returnValue(true));
        $event->expects($this->once())
            ->method('getStart')
            ->will($this->returnValue($startDate));
        $event->expects($this->once())
            ->method('setRemindAt')
            ->with($expectedRemindAt);
        $event->expects($this->once())
            ->method('setReminded')
            ->with(false);

        $this->subscriber->prePersist($args);
    }

    public function testPrePersistForEventWithoutReminder()
    {
        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new LifecycleEventArgs($event, $this->em);

        $event->expects($this->once())
            ->method('getReminder')
            ->will($this->returnValue(false));
        $event->expects($this->never())
            ->method('setRemindAt');
        $event->expects($this->once())
            ->method('setReminded')
            ->with(false);

        $this->subscriber->prePersist($args);
    }

    public function testPreUpdateStartDateAndReminderWasNotChanged()
    {
        $changeSet = array('title' => array('old', 'new'));

        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new PreUpdateEventArgs($event, $this->em, $changeSet);

        $event->expects($this->never())
            ->method('setRemindAt');
        $event->expects($this->never())
            ->method('setReminded');

        $this->uow->expects($this->never())
            ->method('recomputeSingleEntityChangeSet');

        $this->subscriber->preUpdate($args);
    }

    public function testPreUpdateReminderSetsToFalse()
    {
        $expectedRemindAt = null;

        $changeSet = array('reminder' => array(true, false));

        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new PreUpdateEventArgs($event, $this->em, $changeSet);

        $event->expects($this->once())
            ->method('setRemindAt')
            ->with($expectedRemindAt);
        $event->expects($this->never())
            ->method('setReminded');

        $meta = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroCalendarBundle:CalendarEvent')
            ->will($this->returnValue($meta));
        $this->uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->identicalTo($meta), $this->identicalTo($event));

        $this->subscriber->preUpdate($args);
    }

    public function testPreUpdateReminderSetsToTrue()
    {
        $startDate        = new \DateTime('now', new \DateTimeZone('UTC'));
        $expectedRemindAt = clone $startDate;
        $expectedRemindAt->sub(new \DateInterval('PT15M'));

        $changeSet = array('reminder' => array(false, true));

        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new PreUpdateEventArgs($event, $this->em, $changeSet);

        $event->expects($this->once())
            ->method('getStart')
            ->will($this->returnValue($startDate));
        $event->expects($this->once())
            ->method('setRemindAt')
            ->with($expectedRemindAt);
        $event->expects($this->once())
            ->method('getReminded')
            ->will($this->returnValue(true));
        $event->expects($this->once())
            ->method('setReminded')
            ->with(false);

        $meta = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroCalendarBundle:CalendarEvent')
            ->will($this->returnValue($meta));
        $this->uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->identicalTo($meta), $this->identicalTo($event));

        $this->subscriber->preUpdate($args);
    }

    public function testPreUpdateStartDateWasChangedAndReminderIsFalseAndRemindedIsFalse()
    {
        $startDate    = new \DateTime('now', new \DateTimeZone('UTC'));
        $oldStartDate = clone $startDate;
        $oldStartDate->sub(new \DateInterval('PT1H'));

        $changeSet = array('start' => array($oldStartDate, $startDate));

        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new PreUpdateEventArgs($event, $this->em, $changeSet);

        $event->expects($this->never())
            ->method('getStart');
        $event->expects($this->never())
            ->method('setRemindAt');
        $event->expects($this->once())
            ->method('getReminder')
            ->will($this->returnValue(false));
        $event->expects($this->once())
            ->method('getReminded')
            ->will($this->returnValue(false));
        $event->expects($this->never())
            ->method('setReminded');

        $this->uow->expects($this->never())
            ->method('recomputeSingleEntityChangeSet');

        $this->subscriber->preUpdate($args);
    }

    public function testPreUpdateStartDateWasChangedAndReminderIsFalseAndRemindedIsTrue()
    {
        $startDate    = new \DateTime('now', new \DateTimeZone('UTC'));
        $oldStartDate = clone $startDate;
        $oldStartDate->sub(new \DateInterval('PT1H'));

        $changeSet = array('start' => array($oldStartDate, $startDate));

        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new PreUpdateEventArgs($event, $this->em, $changeSet);

        $event->expects($this->never())
            ->method('getStart');
        $event->expects($this->never())
            ->method('setRemindAt');
        $event->expects($this->once())
            ->method('getReminder')
            ->will($this->returnValue(false));
        $event->expects($this->once())
            ->method('getReminded')
            ->will($this->returnValue(true));
        $event->expects($this->once())
            ->method('setReminded')
            ->with(false);

        $meta = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroCalendarBundle:CalendarEvent')
            ->will($this->returnValue($meta));
        $this->uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->identicalTo($meta), $this->identicalTo($event));

        $this->subscriber->preUpdate($args);
    }

    public function testPreUpdateStartDateWasChangedAndReminderIsTrueAndRemindedIsFalse()
    {
        $startDate    = new \DateTime('now', new \DateTimeZone('UTC'));
        $oldStartDate = clone $startDate;
        $oldStartDate->sub(new \DateInterval('PT1H'));
        $expectedRemindAt = clone $startDate;
        $expectedRemindAt->sub(new \DateInterval('PT15M'));

        $changeSet = array('start' => array($oldStartDate, $startDate));

        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new PreUpdateEventArgs($event, $this->em, $changeSet);

        $event->expects($this->never())
            ->method('getStart');
        $event->expects($this->once())
            ->method('setRemindAt')
            ->with($expectedRemindAt);
        $event->expects($this->once())
            ->method('getReminder')
            ->will($this->returnValue(true));
        $event->expects($this->once())
            ->method('getReminded')
            ->will($this->returnValue(false));
        $event->expects($this->never())
            ->method('setReminded');

        $meta = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroCalendarBundle:CalendarEvent')
            ->will($this->returnValue($meta));
        $this->uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->identicalTo($meta), $this->identicalTo($event));

        $this->subscriber->preUpdate($args);
    }

    public function testPreUpdateStartDateWasChangedAndReminderIsTrueAndRemindedIsTrue()
    {
        $startDate    = new \DateTime('now', new \DateTimeZone('UTC'));
        $oldStartDate = clone $startDate;
        $oldStartDate->sub(new \DateInterval('PT1H'));
        $expectedRemindAt = clone $startDate;
        $expectedRemindAt->sub(new \DateInterval('PT15M'));

        $changeSet = array('start' => array($oldStartDate, $startDate));

        $event = $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $args  = new PreUpdateEventArgs($event, $this->em, $changeSet);

        $event->expects($this->never())
            ->method('getStart');
        $event->expects($this->once())
            ->method('setRemindAt')
            ->with($expectedRemindAt);
        $event->expects($this->once())
            ->method('getReminder')
            ->will($this->returnValue(true));
        $event->expects($this->once())
            ->method('getReminded')
            ->will($this->returnValue(true));
        $event->expects($this->once())
            ->method('setReminded')
            ->with(false);

        $meta = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroCalendarBundle:CalendarEvent')
            ->will($this->returnValue($meta));
        $this->uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->identicalTo($meta), $this->identicalTo($event));

        $this->subscriber->preUpdate($args);
    }

    public function testOnFlush()
    {
        $args = new OnFlushEventArgs($this->em);

        $user        = new User();
        $newCalendar = new Calendar();
        $newCalendar->setOwner($user);
        $newCalendar->addConnection(new CalendarConnection($newCalendar));

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));
        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue(array($user)));
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($newCalendar));
        $this->uow->expects($this->once())
            ->method('computeChangeSets');

        $this->subscriber->onFlush($args);
    }
}
