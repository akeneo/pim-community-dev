<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Event\Handler;


use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler;

class EmailNotificationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('createQuery', 'getRepository'))
            ->getMock();

        $this->twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mailer = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new EmailNotificationHandler($this->twig, $this->mailer, $this->entityManager, 'a@a.com');
    }

    protected function tearDown()
    {
        unset($this->entityManager);
    }

    public function testAddJob()
    {
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('getOneOrNullResult')
            ->will($this->returnValue(null));
        $query->expects($this->exactly(2))->method('setParameter')
            ->will($this->returnSelf());
        $query->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());

        $this->entityManager->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($query));

        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->handler->addJob('some:command', array());
    }
}
