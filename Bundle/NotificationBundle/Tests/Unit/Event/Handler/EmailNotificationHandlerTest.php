<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Event\Handler;

use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Symfony\Component\Security\Core\SecurityContextInterface;

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

    /**
     * @var EmailNotificationHandler
     */
    protected $handler;

    /**
     * @var Monolog\Logger
     */
    protected $logger;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mailer = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->handler = new EmailNotificationHandler(
            $this->twig,
            $this->mailer,
            $this->entityManager,
            'a@a.com',
            $this->logger,
            $this->securityContext
        );
        $this->handler->setEnv('prod');
        $this->handler->setMessageLimit(10);
    }

    protected function tearDown()
    {
        unset($this->entityManager);
        unset($this->twig);
        unset($this->mailer);
        unset($this->handler);
    }

    /**
     * Test handler
     */
    public function testHandle()
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $event = $this->getMock('Oro\Bundle\NotificationBundle\Event\NotificationEvent', array(), array($entity));
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $templateContent = "@subject = Test Subj\n@entityName = TestEntity";

        $template = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $template->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($templateContent));
        $template->expects($this->exactly(2))
            ->method('getType')
            ->will($this->returnValue('html'));
        $template->expects($this->once())
            ->method('getSubject')
            ->will($this->returnValue('Test Subj'));

        $notification = $this->getMock('Oro\Bundle\NotificationBundle\Entity\EmailNotification');
        $notification->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue($template));

        $recipientList = $this->getMock('Oro\Bundle\NotificationBundle\Entity\RecipientList');
        $notification->expects($this->once())
            ->method('getRecipientList')
            ->will($this->returnValue($recipientList));

        $notifications = array(
            $notification,
        );

        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');

        $repo = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Entity\Repository\RecipientListRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getRecipientEmails')
            ->with($recipientList, $entity)
            ->will($this->returnValue(array('a@aa.com')));

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with('Oro\Bundle\NotificationBundle\Entity\RecipientList')
            ->will($this->returnValue($repo));

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf('\Swift_Message'));

        $this->addJob();

        $this->handler->handle($event, $notifications);
    }

    /**
     * Test handler with expection and empty recipients
     */
    public function testHandleErrors()
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $event = $this->getMock('Oro\Bundle\NotificationBundle\Event\NotificationEvent', array(), array($entity));
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $templateContent = "@subject = Test Subj\n@entityName = TestEntity";
        $template = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $template->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($templateContent));
        $template->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('html'));

        $notification = $this->getMock('Oro\Bundle\NotificationBundle\Entity\EmailNotification');
        $notification->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue($template));

        $recipientList = $this->getMock('Oro\Bundle\NotificationBundle\Entity\RecipientList');
        $notification->expects($this->once())
            ->method('getRecipientList')
            ->will($this->returnValue($recipientList));

        $notifications = array(
            $notification,
        );

        $this->twig->expects($this->once())
            ->method('render')
            ->will($this->throwException(new \Twig_Error('bla bla bla')));

        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');

        $repo = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Entity\Repository\RecipientListRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getRecipientEmails')
            ->with($recipientList, $entity)
            ->will($this->returnValue(array()));

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with('Oro\Bundle\NotificationBundle\Entity\RecipientList')
            ->will($this->returnValue($repo));

        $this->handler->handle($event, $notifications);
    }

    /**
     * add job assertions
     */
    public function addJob()
    {
        $query = $this->getMock(
            'Doctrine\ORM\AbstractQuery',
            array('getSQL', 'setMaxResults', 'getOneOrNullResult', 'setParameter', '_doExecute'),
            array(),
            '',
            false
        );

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
    }
}
