<?php

namespace Specification\Akeneo\Platform\JobAutomation\Application\NotifyUsers;

use Akeneo\Platform\JobAutomation\Application\NotifyUsers\NotifyUsersInvalidJobInstanceCommand;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByUserGroupIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\UserNotifierInterface;
use PhpSpec\ObjectBehavior;

class NotifyUsersInvalidJobInstanceHandlerSpec extends ObjectBehavior
{
    public function let(
        FindUsersByIdQueryInterface $findUsersByIdQuery,
        FindUsersByUserGroupIdQueryInterface $findUsersByUserGroupIdQuery,
        UserNotifierInterface $userNotifier,
    ) {
        $this->beConstructedWith(
            $findUsersByIdQuery,
            $findUsersByUserGroupIdQuery,
            $userNotifier,
        );
    }

    public function it_calls_the_user_notifier_with_correct_users(
        FindUsersByIdQueryInterface $findUsersByIdQuery,
        FindUsersByUserGroupIdQueryInterface $findUsersByUserGroupIdQuery,
        UserNotifierInterface $userNotifier,
    ) {
        $jobInstance = new ScheduledJobInstance('job_1', 'a_job','import', [], [5, 2], [1, 2], true, '0 0/4 * * *', new \DateTimeImmutable('2022-10-30 00:00'), null, 'job_automated_1');

        $notifiedJulia = new UserToNotify('julia', 'julia@example.com');
        $notifiedPeter = new UserToNotify('peter', 'peter@example.com');
        $notifiedMichel = new UserToNotify('michel', 'michel@example.com');

        $command = new NotifyUsersInvalidJobInstanceCommand(
            'There is an error',
            $jobInstance,
            [1, 2],
            [5, 2]
        );

        $findUsersByIdQuery->execute([5, 2])
            ->willReturn([$notifiedJulia, $notifiedPeter]);
        $findUsersByUserGroupIdQuery->execute([1, 2])
            ->willReturn([$notifiedJulia, $notifiedPeter, $notifiedMichel]);

        $userNotifier->forInvalidJobInstance(
            [$notifiedJulia, $notifiedPeter, $notifiedMichel],
            $jobInstance,
            'There is an error',
        )->shouldBeCalled();

        $this->handle($command);
    }
}
