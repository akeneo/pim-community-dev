<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\JobProfileAccessRepository;
use Prophecy\Argument;

class JobProfileAccessManagerSpec extends ObjectBehavior
{
    function let(SmartManagerRegistry $registry, ObjectManager $objectManager, JobProfileAccessRepository $repository)
    {
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);
        $registry->getRepository(Argument::any())->willReturn($repository);

        $this->beConstructedWith($registry, 'PimEnterprise\Bundle\SecurityBundle\Entity\JobProfileAccess');
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
        $objectManager,
        Group $user,
        Group $admin
    ) {
        $jobProfile->getId()->willReturn(1);
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($jobProfile, [$admin, $user])->shouldBeCalled();

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\JobProfileAccess'))
            ->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($jobProfile, [$user, $admin], [$admin]);
    }

    function it_does_not_revoke_access_to_a_job_profile_on_creation(
        JobInstance $jobProfile,
        $repository,
        $objectManager,
        Group $user,
        Group $admin
    ) {
        $jobProfile->getId()->willReturn(null);
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($jobProfile, Argument::any())->shouldNotBeCalled();

        $objectManager
                ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\JobProfileAccess'))
                ->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($jobProfile, [$user, $admin], [$admin]);
    }
}
