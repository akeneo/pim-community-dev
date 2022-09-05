<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use PhpSpec\ObjectBehavior;

final class PimNotificationNotifierSpec extends ObjectBehavior
{
    public function let(
        NotifierInterface $pimNotifier
    ): void {
        $this->beConstructedWith($pimNotifier);
    }

    public function it_create_and_send_notification_for_invalid_job_instance(
        NotifierInterface $pimNotifier
    ): void
    {
        $jobInstance = new ScheduledJobInstance(
            code: 'test_job',
            jobName: 'test_job',
            type: 'test_scheduled',
            rawParameters: [],
            notifiedUsers: [],
            notifiedUserGroups: [],
            isScheduled: true,
            cronExpression: "",
            setupDate: new \DateTimeImmutable("now"),
            lastExecutionDate: null,
            runningUsername: "test_user"
        );
        $errorMessage = 'something went wrong';
        $usersToNotify = [
            new UserToNotify('test user 1', 'test_user_1@mail.com'),
            new UserToNotify('test user 2', 'test_user_2@mail.com')
        ];

        $expectedNotification = new Notification();
        $expectedNotification
            ->setType('error')
            ->setMessage('akeneo.job_automation.notification.invalid_job_instance')
            ->setMessageParams([
                '{{ label }}' => $jobInstance->code,
                '{{ error }}' => $errorMessage,
            ])
            ->setRoute(sprintf('pim_importexport_%s_profile_show', $jobInstance->type))
            ->setRouteParams(['code' => $jobInstance->code])
            ->setContext(['actionType' => $jobInstance->type]);

        $pimNotifier->notify($expectedNotification, ['test user 1', 'test user 2'])->shouldBeCalled();

        $this->forInvalidJobInstance($usersToNotify, $jobInstance, $errorMessage);
    }
}
