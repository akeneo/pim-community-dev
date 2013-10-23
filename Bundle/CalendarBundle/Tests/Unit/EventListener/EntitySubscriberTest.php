<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\EventListener;

use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarConnection;
use Oro\Bundle\CalendarBundle\EventListener\EntitySubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\CalendarBundle\Notification\RemindTimeCalculator;
use Oro\Bundle\UserBundle\Entity\User;

class EntitySubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testOnFlush()
    {
        $subscriber = new EntitySubscriber(new RemindTimeCalculator(15));
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $args = new OnFlushEventArgs($em);

        $user = new User();
        $newCalendar = new Calendar();
        $newCalendar->setOwner($user);
        $newCalendar->addConnection(new CalendarConnection($newCalendar));

        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));
        $uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue(array($user)));
        $em->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($newCalendar));
        $uow->expects($this->once())
            ->method('computeChangeSets');

        $subscriber->onFlush($args);
    }
}
