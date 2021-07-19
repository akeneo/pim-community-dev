<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swift_Mailer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class MailNotifierSpec extends ObjectBehavior
{
    function let(
        BatchLogHandler $handler,
        TokenStorageInterface $tokenStorage,
        Environment $twig,
        Swift_Mailer $mailer
    ) {
        $this->beConstructedWith($handler, $tokenStorage, $twig, $mailer, 'null://localhost?encryption=tls&auth_mode=login&username=foo&password=bar&sender_address=no-reply@example.com');
        $this->setRecipientEmail('destEmail');
    }

    function it_notifies(JobExecution $jobExecution, $mailer, \Swift_Message $message)
    {
        $mailer->createMessage()->willReturn($message);
        $message->setSubject('Job has been executed')->shouldBeCalled();
        $message->setFrom('no-reply@example.com')->shouldBeCalled();
        $message->setTo('destEmail')->shouldBeCalled();
        $message->setBody(Argument::any(), 'text/plain')->shouldBeCalled();
        $message->addPart(Argument::any(), 'text/html')->shouldBeCalled();
        $mailer->send($message)->shouldBeCalled();

        $this->notify($jobExecution);
    }
}
