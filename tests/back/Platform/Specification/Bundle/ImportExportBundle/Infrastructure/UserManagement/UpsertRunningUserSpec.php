<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserHandlerInterface;
use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;

class UpsertRunningUserSpec extends ObjectBehavior
{
    public function let(
        UpsertUserHandlerInterface $upsertUserHandler,
        RoleRepositoryInterface $roleRepository,
    ) {
        $this->beConstructedWith($upsertUserHandler, $roleRepository);
    }

    public function it_call_upsert_the_user_through_user_management_public_api(
        UpsertUserHandlerInterface $upsertUserHandler,
        RoleRepositoryInterface $roleRepository,
        RoleInterface $administratorRole,
        RoleInterface $userRole
    ) {
        $administratorRole->getRole()->willReturn('ROLE_ADMINISTRATOR');
        $userRole->getRole()->willReturn('ROLE_USER');
        $roleRepository->findAll()->willReturn([$administratorRole, $userRole]);
        $command = UpsertUserCommand::job(
            'job_automated_my_job_name',
            'fakepassword',
            'job_automated_my_job_name@fake.com',
            'my_job_name',
            'Automated Job',
            ['ROLE_ADMINISTRATOR', 'ROLE_USER'],
            ['IT Support'],
        );

        $upsertUserHandler->handle($command)->shouldBeCalledOnce();

        $this->execute('my_job_name', ['IT Support']);
    }
}
