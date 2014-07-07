<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\Role;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\JobProfileAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

class JobProfileAccessManagerSpec extends ObjectBehavior
{
    function let(SmartManagerRegistry $registry, ObjectManager $objectManager, JobProfileAccessRepository $repository)
    {
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);
        $registry->getRepository(Argument::any())->willReturn($repository);

        $this->beConstructedWith($registry, 'PimEnterprise\Bundle\SecurityBundle\Entity\JobProfileAccess');
    }

    function it_provides_roles_that_have_access_to_a_job_profile(JobInstance $jobProfile, $repository)
    {
        $repository->getGrantedRoles($jobProfile, JobProfileVoter::EXECUTE_JOB_PROFILE)->willReturn(['foo', 'bar']);
        $repository->getGrantedRoles($jobProfile, JobProfileVoter::EDIT_JOB_PROFILE)->willReturn(['bar']);

        $this->getExecuteRoles($jobProfile)->shouldReturn(['foo', 'bar']);
        $this->getEditRoles($jobProfile)->shouldReturn(['bar']);
    }

    function it_grants_access_on_a_job_profile_for_the_provided_roles(
        JobInstance $jobProfile,
        $repository,
        $objectManager,
        Role $user,
        Role $admin
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

    function it_should_not_revoke_access_to_a_job_profile_on_creation(
        JobInstance $jobProfile,
        $repository,
        $objectManager,
        Role $user,
        Role $admin
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
