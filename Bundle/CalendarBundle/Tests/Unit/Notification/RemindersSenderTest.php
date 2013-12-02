<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Notification;

use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Notification\EmailNotificationAdapter;
use Oro\Bundle\CronBundle\Command\Logger\Exception\RaiseExceptionLoggerException;
use Oro\Bundle\UserBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Oro\Bundle\CalendarBundle\Notification\RemindersSender;
use Oro\Bundle\CalendarBundle\Notification\RemindTimeCalculator;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;

class RemindersSenderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $eventRepo;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $templateRepo;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $notificationProcessor;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var RemindersSender */
    protected $sender;

    protected function setUp()
    {
        $this->em                    =
            $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock();
        $this->eventRepo             =
            $this->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\Repository\CalendarEventRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $this->templateRepo          =
            $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $this->notificationProcessor =
            $this->getMockBuilder('Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor')
                ->disableOriginalConstructor()
                ->getMock();
        $this->logger                =
            $this->getMock('Psr\Log\LoggerInterface');

        $this->em->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    array(
                        array('OroCalendarBundle:CalendarEvent', $this->eventRepo),
                        array('OroEmailBundle:EmailTemplate', $this->templateRepo)
                    )
                )
            );

        $this->sender = new RemindersSender($this->em, $this->notificationProcessor);
        $this->sender->setLogger($this->logger);
    }

    public function testSendNoReminders()
    {
        $this->setUpEvents(array());

        $this->logger->expects($this->once())
            ->method('notice')
            ->with($this->stringContains('Exit'));
        $this->templateRepo->expects($this->never())
            ->method('findByName');

        $this->sender->send();
    }

    public function testSendNoTemplate()
    {
        $this->setUpEvents(array(new CalendarEvent()));

        $this->templateRepo->expects($this->once())
            ->method('findByName')
            ->with(RemindersSender::EMAIL_TEMPLATE_NAME)
            ->will($this->returnValue(null));

        $this->setExpectedException('\RuntimeException');
        $this->sender->send();
    }

    public function testSend()
    {
        $template = new EmailTemplate();

        // $event1 - fails with RaiseExceptionLoggerException
        $email1 = '1@example.com';
        $event1 = new CalendarEvent();
        $this->setUpEvent($event1, 1, $email1);
        $adapter1 = new EmailNotificationAdapter($template, $email1);

        // $event2 - fails with Exception
        $email2 = '2@example.com';
        $event2 = new CalendarEvent();
        $this->setUpEvent($event2, 2, $email2);
        $adapter2 = new EmailNotificationAdapter($template, $email2);

        // $event3 - success
        $email3 = '3@example.com';
        $event3 = new CalendarEvent();
        $this->setUpEvent($event3, 3, $email3);
        $adapter3 = new EmailNotificationAdapter($template, $email3);

        $this->setUpEvents(array($event1, $event2, $event3));

        $this->templateRepo->expects($this->any())
            ->method('findByName')
            ->with(RemindersSender::EMAIL_TEMPLATE_NAME)
            ->will($this->returnValue($template));

        $this->notificationProcessor->expects($this->at(0))
            ->method('process')
            ->with($this->identicalTo($event1), $this->equalTo(array($adapter1)))
            ->will($this->throwException(new RaiseExceptionLoggerException()));
        $this->notificationProcessor->expects($this->at(1))
            ->method('process')
            ->with($this->identicalTo($event2), $this->equalTo(array($adapter2)))
            ->will($this->throwException(new \Exception()));
        $this->notificationProcessor->expects($this->at(2))
            ->method('process')
            ->with($this->identicalTo($event3), $this->equalTo(array($adapter3)));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('A reminder sending failed.'), $this->arrayHasKey('exception'));

        $this->logger->expects($this->once())
            ->method('notice')
            ->with($this->stringContains('1 reminder'));

        $this->em->expects($this->once())
            ->method('flush');

        $this->setExpectedException(
            '\RuntimeException',
            'The sending of reminders failed for the following calendar events: 1, 2.'
        );

        try {
            $this->sender->send();
        } catch (\Exception $ex) {
            $this->assertFalse($event1->getReminded());
            $this->assertFalse($event2->getReminded());
            $this->assertTrue($event3->getReminded());

            throw $ex;
        }
    }

    protected function setUpEvents(array $events)
    {
        $qb    = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockForAbstractClass(
            'Doctrine\ORM\AbstractQuery',
            array(),
            '',
            false,
            false,
            true,
            array('getResult')
        );
        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($events));
        $this->eventRepo->expects($this->once())
            ->method('getEventsToRemindQueryBuilder')
            ->will($this->returnValue($qb));
    }

    protected function setUpEvent(CalendarEvent $event, $id, $email)
    {
        $user = new User();
        $user->setEmail($email);

        $calendar = new Calendar();
        $calendar->setOwner($user);

        $event->setCalendar($calendar);

        ReflectionUtil::setPrivateProperty($event, 'id', $id);
        $event->setReminded(false);
    }
}
