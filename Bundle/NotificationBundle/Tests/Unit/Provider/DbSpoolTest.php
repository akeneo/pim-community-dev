<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler;
use Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface;
use Oro\Bundle\NotificationBundle\Provider\Mailer\DbSpool;

class DbSpoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DbSpool
     */
    protected $spool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var EventHandlerInterface
     */
    protected $handler;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->className = 'Oro\Bundle\NotificationBundle\Entity\SpoolItem';

        $this->spool = new DbSpool($this->em, $this->className);

        $this->spool->start();
        $this->spool->stop();
        $this->assertTrue($this->spool->isStarted());
    }

    /**
     * Test adding to spool/queueing message
     *
     * @param bool $flush
     * @dataProvider queueMessageDataProvider
     */
    public function testQueueMessage($flush)
    {
        $message = $this->getMock('\Swift_Mime_Message');

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf($this->className));

        if ($flush) {
            $this->em
                ->expects($this->once())
                ->method('flush')
                ->with($this->isInstanceOf($this->className));
        } else {
            $this->em
                ->expects($this->never())
                ->method('flush');
        }

        $this->spool->setFlushOnQueue($flush);
        $this->assertTrue($this->spool->queueMessage($message));
    }

    /**
     * @return array
     */
    public function queueMessageDataProvider()
    {
        return array(
            'with flush' => array(
                'flush' => true,
            ),
            'without flush' => array(
                'flush' => false,
            ),
        );
    }

    /**
     * Test adding to spool/queueing message
     * @expectedException \Swift_IoException
     */
    public function testQueueMessageException()
    {
        $message = $this->getMock('\Swift_Mime_Message');

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf($this->className));

        $this->em
            ->expects($this->once())
            ->method('flush')
            ->will($this->throwException(new \Exception('problem')));

        $this->spool->setFlushOnQueue(true);
        $this->spool->queueMessage($message);
    }

    public function testFlushMessage()
    {
        $transport = $this->getMock('\Swift_Transport');

        $transport->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));
        $transport->expects($this->once())
            ->method('start');

        $message = $this->getMock('\Swift_Mime_Message');
        $messageSerialized = serialize($message);

        $spoolItem = $this->getMock($this->className);
        $spoolItem->expects($this->once())
            ->method('setStatus');
        $spoolItem->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue($messageSerialized));
        $emails = array(
            $spoolItem,
        );

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf($this->className));
        $this->em
            ->expects($this->exactly(2))
            ->method('flush');
        $this->em
            ->expects($this->once())
            ->method('remove');

        $repository = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Entity\Repository\SpoolItemRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue($emails));

        $this->em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->will($this->returnValue($repository));

        $transport->expects($this->once())
            ->method('send')
            ->with($message, array())
            ->will($this->returnValue(1));

        $this->spool->setTimeLimit(-100);
        $count = $this->spool->flushQueue($transport);
        $this->assertEquals(1, $count);
    }

    public function testFlushMessageZeroEmails()
    {
        $transport = $this->getMock('\Swift_Transport');

        $transport->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));
        $transport->expects($this->once())
            ->method('start');

        $repository = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Entity\Repository\SpoolItemRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue(array()));

        $this->em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->will($this->returnValue($repository));

        $count = $this->spool->flushQueue($transport);
        $this->assertEquals(0, $count);
    }
}
