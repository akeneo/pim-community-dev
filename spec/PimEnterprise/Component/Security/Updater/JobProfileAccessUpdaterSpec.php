<?php

namespace spec\PimEnterprise\Component\Security\Updater;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Component\Security\Model\JobProfileAccessInterface;

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
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_job_profile_access()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'PimEnterprise\Component\Security\Model\JobProfileAccessInterface'
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
                'PimEnterprise\Component\Security\Updater\JobProfileAccessUpdater',
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
                'PimEnterprise\Component\Security\Updater\JobProfileAccessUpdater',
                'foo'
            )
        )->during('update', [$jobProfileAccess, ['job_profile' => 'foo']]);
    }
}
