<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifier;
use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class MailNotifierSpec extends ObjectBehavior
{
    function let(
        LoggerInterface $logger,
        TokenStorageInterface $tokenStorage,
        Environment $twig,
        MailNotifierInterface $mailer
    ): void {
        $twig->render(Argument::type('string'), Argument::type('array'))->willReturn('');
        $this->beConstructedWith($logger, $tokenStorage, $twig, $mailer, 'null://localhost?encryption=tls&auth_mode=login&username=foo&password=bar');
        $this->setRecipients(['test@akeneo.com']);
    }

    public function it_notifies_success(JobExecution $jobExecution, $mailer): void
    {
        $batchStatus = new BatchStatus(BatchStatus::COMPLETED);
        $jobExecution->getStatus()->willReturn($batchStatus);

        $mailer->notify(
            ['test@akeneo.com'],
            'Akeneo completed your export: success',
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();

        $this->notify($jobExecution);
    }

    public function it_notifies_fail(JobExecution $jobExecution, $mailer): void
    {
        $batchStatus = new BatchStatus(BatchStatus::UNKNOWN);
        $jobExecution->getStatus()->willReturn($batchStatus);

        $mailer->notify(
            ['test@akeneo.com'],
            'Akeneo completed your export: fail',
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();

        $this->notify($jobExecution);
    }

    public function it_should_log_error_if_notification_failed(JobExecution $jobExecution, $mailer, $logger): void
    {
        $batchStatus = new BatchStatus(BatchStatus::COMPLETED);
        $jobExecution->getStatus()->willReturn($batchStatus);

        $mailer->notify(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willThrow(\Throwable::class);

        $logger->error(Argument::any(), Argument::any())->shouldBeCalled();

        $this->notify($jobExecution);
    }
}
