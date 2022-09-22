<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\UseCases;

use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Akeneo\Platform\JobAutomation\Domain\Model\DueJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Test\Acceptance\AcceptanceTestCase;
use AkeneoTest\Platform\Acceptance\NotificationBundle\FakeService\FakeMailNotifier;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SendEmailWhenJobInstanceCannotBeLaunchedTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_does_not_sent_an_email_when_automated_job_is_invalid_an_there_is_no_user_to_notify(): void
    {
        $this->dispatchInvalidJobInstance([]);

        $this->getMailer()->assertNoEmailHaveBeenSent();
    }

    /**
     * @test
     */
    public function it_sent_an_email_when_automated_job_cannot_be_launched_due_to_invalid_job_instance(): void
    {
        $this->dispatchInvalidJobInstance([
            new UserToNotify('admin', 'admin@akeneo.com'),
            new UserToNotify('julia', 'julia@akeneo.com'),
        ]);

        $this->getMailer()->assertEmailHaveBeenSent('admin@akeneo.com', 'Could not launch scheduled job instance');
        $this->getMailer()->assertEmailHaveBeenSent('julia@akeneo.com', 'Could not launch scheduled job instance');
    }

    /**
     * @test
     */
    public function it_sent_an_email_when_automated_job_cannot_be_launched_due_to_internal_error(): void
    {
        $this->dispatchInternalErrorDuringJobScheduling([
            new UserToNotify('admin', 'admin@akeneo.com'),
            new UserToNotify('julia', 'julia@akeneo.com'),
        ]);

        $this->getMailer()->assertEmailHaveBeenSent('admin@akeneo.com', 'Could not launch scheduled job instance');
        $this->getMailer()->assertEmailHaveBeenSent('julia@akeneo.com', 'Could not launch scheduled job instance');
    }

    private function dispatchInvalidJobInstance(array $usersToNotify): void
    {
        $event = CouldNotLaunchAutomatedJobEvent::dueToInvalidJobInstance(
            new DueJobInstance($this->getScheduledJobInstance(), new UserToNotifyCollection($usersToNotify)),
            ['error1', 'error2'],
        );

        $this->getEventDispatcher()->dispatch($event);
    }

    private function dispatchInternalErrorDuringJobScheduling(array $usersToNotify): void
    {
        $event = CouldNotLaunchAutomatedJobEvent::dueToInvalidJobInstance(
            new DueJobInstance($this->getScheduledJobInstance(), new UserToNotifyCollection($usersToNotify)),
            ['error1', 'error2'],
        );

        $this->getEventDispatcher()->dispatch($event);
    }

    private function getScheduledJobInstance(): ScheduledJobInstance
    {
        return new ScheduledJobInstance(
            'job_code',
            'job_label',
            'export',
            [],
            [],
            [],
            '* * * * *',
            new \DateTimeImmutable('2022-10-30 00:00'),
            null,
            'job_automated_job_code',
        );
    }

    private function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->get('event_dispatcher');
    }

    private function getMailer(): FakeMailNotifier
    {
        return $this->get('pim_notification.email.email_notifier');
    }
}
