<?php

namespace Specification\Akeneo\Platform\JobAutomation\Application\NotifyUsers;

use Akeneo\Platform\JobAutomation\Application\NotifyUsers\NotifyUsersInvalidJobInstanceCommand;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByUserGroupIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\UserNotifierInterface;
use PhpSpec\ObjectBehavior;

class NotifyUsersInvalidJobInstanceHandlerSpec extends ObjectBehavior
{
    public function let(
        UserNotifierInterface $userNotifier,
    ): void {
        $this->beConstructedWith(
            $userNotifier,
        );
    }

    public function it_calls_the_user_notifier_with_correct_users(
        UserNotifierInterface $userNotifier,
    ): void {
        $jobInstance = new ScheduledJobInstance('job_1', 'a_job','import', [], [5, 2], [1, 2], '0 0/4 * * *', new \DateTimeImmutable('2022-10-30 00:00'), null, 'job_automated_1');
        $usersToNotify = new UserToNotifyCollection([
            new UserToNotify('julia', 'julia@example.com'),
            new UserToNotify('peter', 'peter@example.com'),
            new UserToNotify('michel', 'michel@example.com'),
        ]);

        $command = new NotifyUsersInvalidJobInstanceCommand(
            'There is an error',
            $jobInstance,
            $usersToNotify,
        );

        $userNotifier->forInvalidJobInstance(
            $usersToNotify,
            $jobInstance,
            'There is an error',
        )->shouldBeCalled();

        $this->handle($command);
    }
}
