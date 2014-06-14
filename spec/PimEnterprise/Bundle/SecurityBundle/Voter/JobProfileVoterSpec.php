<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

class JobProfileVoterSpec extends ObjectBehavior
{
    protected $attributes = array(JobProfileVoter::EDIT_JOB_PROFILE, JobProfileVoter::EXECUTE_JOB_PROFILE);

    function let(JobProfileAccessManager $accessManager, TokenInterface $token)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_returns_abstain_access_if_non_job_profile_entity($token)
    {
        $this
            ->vote($token, 'foo', array('bar', 'baz'))
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, JobProfileVoter $jobProfile)
    {
        $this
            ->vote($token, $jobProfile, [JobProfileVoter::EDIT_JOB_PROFILE])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_role(
        $accessManager,
        $token,
        JobInstance $jobProfile
    ) {
        $accessManager->getEditRoles($jobProfile)->willReturn(array());
        $accessManager->getExecuteRoles($jobProfile)->willReturn(array());

        $this
            ->vote($token, $jobProfile, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $accessManager,
        $token,
        JobInstance $jobProfile,
        User $user
    ) {
        $token->getUser()->willReturn($user);
        $user->hasRole('foo')->willReturn(false);
        $accessManager->getEditRoles($jobProfile)->willReturn(array('foo'));

        $this
            ->vote($token, $jobProfile, array(JobProfileVoter::EDIT_JOB_PROFILE))
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $accessManager,
        $token,
        JobInstance $jobProfile,
        User $user
    ) {
        $token->getUser()->willReturn($user);
        $user->hasRole('foo')->willReturn(true);
        $accessManager->getExecuteRoles($jobProfile)->willReturn(array('foo'));

        $this
            ->vote($token, $jobProfile, array(JobProfileVoter::EXECUTE_JOB_PROFILE))
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
