<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Notification;

use Pim\Bundle\BatchBundle\Notification\MailNotifier;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MailNotifierTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->handler         = $this->getDisabledConstructorMock(
            'Pim\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler'
        );
        $this->securityContext = $this->getDisabledConstructorMock(
            'Symfony\Component\Security\Core\SecurityContextInterface'
        );
        $this->twig            = $this->getDisabledConstructorMock('\Twig_Environment');
        $this->mailer          = $this->getDisabledConstructorMock('\Swift_Mailer');

        $this->notifier = new MailNotifier(
            $this->handler,
            $this->securityContext,
            $this->twig,
            $this->mailer
        );
    }

    public function testIsANotifier()
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Notification\Notifier', $this->notifier);
    }

    public function testNotifyWithLoggedInUserEmail()
    {
        $user = $this->getUserMock();
        $token = $this->getTokenMock($user);
        $this->securityContext
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->handler
            ->expects($this->once())
            ->method('getRealPath')
            ->will($this->returnValue('/tmp/foo.log'));

        $jobExecution = $this->getDisabledConstructorMock('Pim\Bundle\BatchBundle\Entity\JobExecution');
        $parameters = array(
            'user'         => $user,
            'jobExecution' => $jobExecution,
            'log'          => '/tmp/foo.log',
        );
        $this->twig
            ->expects($this->exactly(2))
            ->method('render')
            ->will(
                $this->returnValueMap(
                    array(
                        array('PimBatchBundle:Mails:notification.txt.twig', $parameters, 'notification'),
                        array('PimBatchBundle:Mails:notification.html.twig', $parameters, '<p>notification</p>'),
                    )
                )
            );

        $message = $this->getDisabledConstructorMock('\Swift_Message');
        $this->mailer
            ->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $message->expects($this->once())
            ->method('setSubject')
            ->with('Job has been executed');

        $message->expects($this->once())
            ->method('setFrom')
            ->with('no-reply@akeneo.com');

        $user->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue('john.doe@example.com'));
        $message->expects($this->once())
            ->method('setTo')
            ->with('john.doe@example.com');
        $message->expects($this->once())
            ->method('setBody')
            ->with('notification', 'text/plain');
        $message->expects($this->once())
            ->method('addPart')
            ->with('<p>notification</p>', 'text/html');

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($message);

        $this->notifier->notify($jobExecution);
    }

    public function testDoNotNotifyIfNoUserLoggedIn()
    {
        $token = $this->getTokenMock(null);

        $this->securityContext
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $jobExecution = $this->getDisabledConstructorMock('Pim\Bundle\BatchBundle\Entity\JobExecution');

        $this->mailer
            ->expects($this->never())
            ->method('send');

        $this->notifier->notify($jobExecution);
    }

    protected function getTokenMock($user)
    {
        $token = $this->getDisabledConstructorMock(
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface'
        );

        $token->expects($this->any())
              ->method('getUser')
              ->will($this->returnValue($user));

        return $token;
    }

    private function getUserMock()
    {
        return $this->getDisabledConstructorMock('Oro\Bundle\UserBundle\Entity\User');
    }

    private function getDisabledConstructorMock($classname)
    {
        return $this
            ->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
