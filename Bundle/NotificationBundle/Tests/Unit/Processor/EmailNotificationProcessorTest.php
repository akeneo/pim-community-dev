<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Processor;

use Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor;
use Monolog\Logger;

class EmailNotificationProcessorTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_CLASS = 'SomeEntity';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $twig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mailer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var EmailNotificationProcessor */
    protected $processor;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $this->twig = $this->getMockBuilder('Oro\Bundle\EmailBundle\Provider\EmailRenderer')
            ->disableOriginalConstructor()->getMock();

        $this->mailer = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()->getMock();

        $this->logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()->getMock();

        $this->processor = new EmailNotificationProcessor(
            $this->twig,
            $this->mailer,
            $this->entityManager,
            'a@a.com',
            $this->logger
        );
        $this->processor->setEnv('prod');
        $this->processor->setMessageLimit(10);
    }

    protected function tearDown()
    {
        unset($this->entityManager);
        unset($this->twig);
        unset($this->securityPolicy);
        unset($this->sandbox);
        unset($this->mailer);
        unset($this->logger);
        unset($this->securityContext);
        unset($this->configProvider);
        unset($this->cache);
        unset($this->processor);
    }

    /**
     * Test processor
     */
    public function testProcess()
    {
        $object = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $notification = $this->getMock('Oro\Bundle\NotificationBundle\Processor\EmailNotificationInterface');
        $notifications = array($notification);

        $template = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $template->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('html'));
        $notification->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue($template));

        $this->twig->expects($this->once())
            ->method('compileMessage')
            ->with($this->identicalTo($template), $this->equalTo(array('entity' => $object)))
            ->will($this->returnValue(array('subject', 'body')));

        $emails = array('email@a.com');
        $notification->expects($this->once())
            ->method('getRecipientEmails')
            ->will($this->returnValue($emails));

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf('\Swift_Message'));

        $this->addJob();

        $this->processor->process($object, $notifications);
    }

    /**
     * Test processor with exception and empty recipients
     */
    public function testProcessErrors()
    {
        $object = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $notification = $this->getMock('Oro\Bundle\NotificationBundle\Processor\EmailNotificationInterface');
        $notifications = array($notification);

        $template = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $notification->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue($template));

        $this->twig->expects($this->once())
            ->method('compileMessage')
            ->will($this->throwException(new \Twig_Error('bla bla bla')));

        $this->logger->expects($this->once())
            ->method('error');

        $notification->expects($this->never())
            ->method('getRecipientEmails');

        $this->mailer->expects($this->never())
            ->method('send');

        $this->processor->process($object, $notifications);
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
        $query->expects($this->exactly(2))
            ->method('setParameter')
            ->will($this->returnSelf());

        $this->entityManager->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($query));

        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->never())
            ->method('flush');
    }
}
