<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater;

use Akeneo\Pim\Permission\Component\Updater\JobProfileAccessUpdater;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\Pim\Permission\Component\Model\JobProfileAccessInterface;

class JobProfileAccessUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith($groupRepository, $jobRepository);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_job_profile_access()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                \stdClass::class,
                JobProfileAccessInterface::class
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_a_job_profile_access(
        $groupRepository,
        $jobRepository,
        JobProfileAccessInterface $jobProfileAccess,
        GroupInterface $userGroup,
        JobInstance $jobInstance
    ) {
        $values = [
            'job_profile'         => 'my_job',
            'user_group'          => 'IT Manager',
            'execute_job_profile' => true,
            'edit_job_profile'    => false,
        ];

        $jobProfileAccess->setJobProfile($jobInstance)->shouldBeCalled();
        $jobProfileAccess->setUserGroup($userGroup)->shouldBeCalled();
        $jobProfileAccess->setExecuteJobProfile(true)->shouldBeCalled();
        $jobProfileAccess->setEditJobProfile(false)->shouldBeCalled();

        $groupRepository->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $jobRepository->findOneByIdentifier('my_job')->willReturn($jobInstance);

        $this->update($jobProfileAccess, $values, []);
    }

    function it_updates_a_job_profile_access_with_edit_permission_only(
        $groupRepository,
        $jobRepository,
        JobProfileAccessInterface $jobProfileAccess,
        GroupInterface $userGroup,
        JobInstance $jobInstance
    ) {
        $values = [
            'job_profile'         => 'my_job',
            'user_group'          => 'IT Manager',
            'execute_job_profile' => false,
            'edit_job_profile'    => true,
        ];

        $jobProfileAccess->setJobProfile($jobInstance)->shouldBeCalled();
        $jobProfileAccess->setUserGroup($userGroup)->shouldBeCalled();
        $jobProfileAccess->setExecuteJobProfile(false)->shouldBeCalled();
        $jobProfileAccess->setExecuteJobProfile(true)->shouldBeCalled();
        $jobProfileAccess->setEditJobProfile(true)->shouldBeCalled();

        $groupRepository->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $jobRepository->findOneByIdentifier('my_job')->willReturn($jobInstance);

        $this->update($jobProfileAccess, $values, []);
    }

    function it_throws_an_exception_if_group_not_found(
        $groupRepository,
        JobProfileAccessInterface $jobProfileAccess
    ) {
        $groupRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'user_group',
                'group code',
                'The group does not exist',
                JobProfileAccessUpdater::class,
                'foo'
            )
        )->during('update', [$jobProfileAccess, ['user_group' => 'foo']]);
    }

    function it_throws_an_exception_if_job_profile_not_found(
        $jobRepository,
        JobProfileAccessInterface $jobProfileAccess
    ) {
        $jobRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'job_profile',
                'job profile code',
                'The job profile does not exist',
                JobProfileAccessUpdater::class,
                'foo'
            )
        )->during('update', [$jobProfileAccess, ['job_profile' => 'foo']]);
    }
}
