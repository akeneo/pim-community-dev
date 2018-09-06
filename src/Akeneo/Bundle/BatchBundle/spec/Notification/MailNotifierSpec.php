<?php

namespace spec\Akeneo\Bundle\BatchBundle\Notification;

use Akeneo\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MailNotifierSpec extends ObjectBehavior
{
    function let(
        BatchLogHandler $handler,
        TokenStorageInterface $tokenStorage,
        \Twig_Environment $twig,
        \Swift_Mailer $mailer
    ) {
        $this->beConstructedWith($handler, $tokenStorage, $twig, $mailer, 'myEmail');
    }

    function it_notifies(JobExecution $jobExecution, $mailer, \Swift_Message $message)
    {
        $this->setRecipientEmail('destEmail');
        $mailer->createMessage()->willReturn($message);
        $message->setSubject('Job has been executed')->shouldBeCalled();
        $message->setFrom('myEmail')->shouldBeCalled();
        $message->setTo('destEmail')->shouldBeCalled();
        $message->setBody(Argument::any(), 'text/plain')->shouldBeCalled();
        $message->addPart(Argument::any(), 'text/html')->shouldBeCalled();
        $mailer->send($message)->shouldBeCalled();

        $this->notify($jobExecution);
    }

    function it_does_not_notify_if_no_recipient_is_provided(JobExecution $jobExecution, $mailer)
    {
        $mailer->send(Argument::any())->shouldNotBeCalled();
        $this->notify($jobExecution);
    }
}
