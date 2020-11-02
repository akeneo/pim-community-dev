<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Voter;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Permission\Bundle\Manager\JobProfileAccessManager;
use Akeneo\Pim\Permission\Bundle\Voter\JobProfileVoter;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class JobProfileVoterSpec extends ObjectBehavior
{
    protected $attributes = [Attributes::EDIT, Attributes::EXECUTE];

    function let(JobProfileAccessManager $accessManager, TokenInterface $token)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_returns_abstain_access_if_non_job_profile_entity($token)
    {
        $this
            ->vote($token, 'foo', ['bar', 'baz'])
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
        JobInstance $jobProfile,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $accessManager->getEditUserGroups($jobProfile)->willReturn([]);
        $accessManager->getExecuteUserGroups($jobProfile)->willReturn([]);

        $this
            ->vote($token, $jobProfile, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $accessManager,
        $token,
        JobInstance $jobProfile,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->hasGroup('foo')->willReturn(false);
        $accessManager->getEditUserGroups($jobProfile)->willReturn(['foo']);

        $this
            ->vote($token, $jobProfile, [Attributes::EDIT])
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $accessManager,
        $token,
        JobInstance $jobProfile,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->hasGroup('foo')->willReturn(true);
        $accessManager->getExecuteUserGroups($jobProfile)->willReturn(['foo']);

        $this
            ->vote($token, $jobProfile, [Attributes::EXECUTE])
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
