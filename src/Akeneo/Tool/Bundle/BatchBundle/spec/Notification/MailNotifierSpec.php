<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
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
        $this->beConstructedWith($logger, $tokenStorage, $twig, $mailer);
        $this->setRecipients(['test@akeneo.com']);
    }

    public function it_notifies_a_successful_job(
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        MailNotifierInterface $mailer,
    ): void {
        $batchStatus = new BatchStatus(BatchStatus::COMPLETED);
        $jobExecution->getStatus()->willReturn($batchStatus);
        $jobInstance->getLabel()->willReturn('An export');
        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $mailer->notify(
            ['test@akeneo.com'],
            'Akeneo successfully completed your "An export" job',
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();

        $this->notify($jobExecution);
    }

    public function it_notifies_a_failed_job(
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        MailNotifierInterface $mailer,
    ): void {
        $batchStatus = new BatchStatus(BatchStatus::UNKNOWN);
        $jobExecution->getStatus()->willReturn($batchStatus);
        $jobInstance->getLabel()->willReturn('Mass Edith');
        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $mailer->notify(
            ['test@akeneo.com'],
            'Akeneo completed your "Mass Edith" job with errors',
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();

        $this->notify($jobExecution);
    }

    public function it_should_log_error_if_notification_failed(
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        MailNotifierInterface $mailer,
        LoggerInterface $logger,
    ): void {
        $batchStatus = new BatchStatus(BatchStatus::COMPLETED);
        $jobExecution->getStatus()->willReturn($batchStatus);
        $jobInstance->getLabel()->willReturn('An export');
        $jobExecution->getJobInstance()->willReturn($jobInstance);

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
