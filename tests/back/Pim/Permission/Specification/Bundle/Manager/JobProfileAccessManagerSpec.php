<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Manager;

use Akeneo\Pim\Permission\Bundle\Entity\JobProfileAccess;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Persistence\ObjectManager;
use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\JobProfileAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Prophecy\Argument;

class JobProfileAccessManagerSpec extends ObjectBehavior
{
    function let(
        JobProfileAccessRepository $repository,
        BulkSaverInterface $saver,
        BulkObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith(
            $repository,
            $saver,
            $detacher,
            JobProfileAccess::class
        );
    }

    function it_provides_user_groups_that_have_access_to_a_job_profile(JobInstance $jobProfile, $repository)
    {
        $repository->getGrantedUserGroups($jobProfile, Attributes::EXECUTE)->willReturn(['foo', 'bar']);
        $repository->getGrantedUserGroups($jobProfile, Attributes::EDIT)->willReturn(['bar']);

        $this->getExecuteUserGroups($jobProfile)->shouldReturn(['foo', 'bar']);
        $this->getEditUserGroups($jobProfile)->shouldReturn(['bar']);
    }

    function it_grants_access_on_a_job_profile_for_the_provided_user_groups(
        JobInstance $jobProfile,
        $repository,
        $saver,
        $detacher,
        Group $user,
        Group $admin
    ) {
        $jobProfile->getId()->willReturn(1);
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($jobProfile, [$admin, $user])->shouldBeCalled();
        $saver->saveAll(Argument::size(2))->shouldBeCalled();
        $detacher->detachAll(Argument::size(2))->shouldBeCalled();

        $this->setAccess($jobProfile, [$user, $admin], [$admin]);
    }

    function it_does_not_revoke_access_to_a_job_profile_on_creation(
        JobInstance $jobProfile,
        $repository,
        $saver,
        $detacher,
        Group $user,
        Group $admin
    ) {
        $jobProfile->getId()->willReturn(null);
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($jobProfile, Argument::any())->shouldNotBeCalled();
        $saver->saveAll(Argument::size(2))->shouldBeCalled();
        $detacher->detachAll(Argument::size(2))->shouldBeCalled();

        $this->setAccess($jobProfile, [$user, $admin], [$admin]);
    }
}
