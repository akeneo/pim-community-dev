<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserHandlerInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\ListUserRoleInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\UserRole;
use PhpSpec\ObjectBehavior;

class UpsertRunningUserSpec extends ObjectBehavior
{
    public function let(
        UpsertUserHandlerInterface $upsertUserHandler,
        ListUserRoleInterface $listUserRole,
        ResolveScheduledJobRunningUsername $resolveScheduledJobRunningUsername,
    ) {
        $this->beConstructedWith($upsertUserHandler, $listUserRole, $resolveScheduledJobRunningUsername);
    }

    public function it_calls_upsert_user_through_user_management_public_api(
        UpsertUserHandlerInterface $upsertUserHandler,
        ListUserRoleInterface $listUserRole,
        ResolveScheduledJobRunningUsername $resolveScheduledJobRunningUsername,
        UserRole $administratorRole,
        UserRole $userRole,
    ): void {
        $resolveScheduledJobRunningUsername->fromJobCode('my_job_name')->shouldBeCalled()->willReturn('job_automated_my_job_name');
        $administratorRole->getRole()->willReturn('ROLE_ADMINISTRATOR');
        $userRole->getRole()->willReturn('ROLE_USER');
        $listUserRole->all()->willReturn([$administratorRole, $userRole]);
        $command = UpsertUserCommand::job(
            'job_automated_my_job_name',
            'fakepassword',
            'job_automated_my_job_name@example.com',
            'my_job_name',
            'Automated Job',
            ['ROLE_ADMINISTRATOR', 'ROLE_USER'],
            ['IT Support'],
        );

        $upsertUserHandler->handle($command)->shouldBeCalledOnce();

        $this->execute('my_job_name', ['IT Support']);
    }
}
