<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Security;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Security\ProjectVoter;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ProjectVoterSpec extends ObjectBehavior
{
    function let(UserRepositoryInterface $userRepository)
    {
        $this->beConstructedWith($userRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectVoter::class);
    }

    function it_is_a_voter()
    {
        $this->shouldHaveType(VoterInterface::class);
    }

    function it_returns_access_granted_for_owner(
        TokenInterface $token,
        ProjectInterface $project,
        UserInterface $owner
    ) {
        $owner->getId()->willReturn(42);

        $project->getOwner()->willReturn($owner);
        $token->getUser()->willReturn($owner);

        $this->vote($token, $project, [ProjectVoter::OWN])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_returns_access_granted_for_contributor(
        $userRepository,
        TokenInterface $token,
        ProjectInterface $project,
        UserInterface $contributor
    ) {
        $token->getUser()->willReturn($contributor);

        $userRepository->isProjectContributor($project, $contributor)->willReturn(true);

        $this->vote($token, $project, [ProjectVoter::CONTRIBUTE])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_returns_access_denied_for_other_users(
        $userRepository,
        TokenInterface $token,
        ProjectInterface $project,
        UserInterface $owner,
        UserInterface $otherUser
    ) {
        $otherUser->getId()->willReturn(13);
        $token->getUser()->willReturn($otherUser);

        $owner->getId()->willReturn(42);
        $project->getOwner()->willReturn($owner);

        $this->vote($token, $project, [ProjectVoter::OWN])->shouldReturn(VoterInterface::ACCESS_DENIED);

        $token->getUser()->willReturn($otherUser);

        $userRepository->isProjectContributor($project, $otherUser)->willReturn(false);

        $this->vote($token, $project, [ProjectVoter::CONTRIBUTE])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_access_denied_if_user_is_null(
        TokenInterface $token,
        ProjectInterface $project
    ) {
        $token->getUser()->willReturn(null);

        $this->vote($token, $project, [ProjectVoter::CONTRIBUTE])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }
}
