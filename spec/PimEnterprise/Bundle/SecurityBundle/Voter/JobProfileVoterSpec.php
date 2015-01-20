<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class JobProfileVoterSpec extends ObjectBehavior
{
    protected $attributes = array(Attributes::EDIT, Attributes::EXECUTE);

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
            ->vote($token, $jobProfile, [Attributes::EDIT])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_group(
        $accessManager,
        $token,
        JobInstance $jobProfile
    ) {
        $accessManager->getEditUserGroups($jobProfile)->willReturn(array());
        $accessManager->getExecuteUserGroups($jobProfile)->willReturn(array());

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
        $user->hasGroup('foo')->willReturn(false);
        $accessManager->getEditUserGroups($jobProfile)->willReturn(array('foo'));

        $this
            ->vote($token, $jobProfile, array(Attributes::EDIT))
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $accessManager,
        $token,
        JobInstance $jobProfile,
        User $user
    ) {
        $token->getUser()->willReturn($user);
        $user->hasGroup('foo')->willReturn(true);
        $accessManager->getExecuteUserGroups($jobProfile)->willReturn(array('foo'));

        $this
            ->vote($token, $jobProfile, array(Attributes::EXECUTE))
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
