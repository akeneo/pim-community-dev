<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserCommand;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserHandlerInterface;
use PhpSpec\ObjectBehavior;

class DeleteRunningUserSpec extends ObjectBehavior
{
    public function let(
        DeleteUserHandlerInterface $deleteUserHandler,
        ResolveScheduledJobRunningUsername $resolveScheduledJobRunningUsername,
    ) {
        $this->beConstructedWith($deleteUserHandler, $resolveScheduledJobRunningUsername);
    }

    public function it_calls_delete_user_through_user_management_public_api(
        DeleteUserHandlerInterface $deleteUserHandler,
        ResolveScheduledJobRunningUsername $resolveScheduledJobRunningUsername,
    ): void {
        $resolveScheduledJobRunningUsername->fromJobCode('my_job_name')->shouldBeCalled()->willReturn('job_automated_my_job_name');
        $command = new DeleteUserCommand('job_automated_my_job_name',);

        $deleteUserHandler->handle($command)->shouldBeCalledOnce();

        $this->execute('my_job_name');
    }
}
