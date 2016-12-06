<?php

namespace spec\Akeneo\ActivityManager\Component\Voter;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\UserRepositoryInterface;
use Akeneo\ActivityManager\Component\Voter\ProjectVoter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;

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

    function it_has_own_and_read_attribute()
    {
        $this->supportsAttribute(ProjectVoter::OWN)->shouldReturn(true);
        $this->supportsAttribute(ProjectVoter::CONTRIBUTE)->shouldReturn(true);
        $this->supportsAttribute('wrong_attribute')->shouldReturn(false);
    }

    function it_only_work_with_project()
    {
        $this->supportsClass(ProjectInterface::class)->shouldReturn(true);
        $this->supportsClass('OtherClass')->shouldReturn(false);
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
        $contributor->getId()->willReturn(13);
        $token->getUser()->willReturn($contributor);

        $userRepository->findContributorToNotify($project)->willReturn([$contributor]);

        $this->vote($token, $project, [ProjectVoter::CONTRIBUTE])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_returns_access_denied_for_other_users(
        $userRepository,
        TokenInterface $token,
        ProjectInterface $project,
        UserInterface $owner,
        UserInterface $contributor,
        UserInterface $otherUser
    ) {
        $contributor->getId()->willReturn(13);
        $token->getUser()->willReturn($otherUser);

        $owner->getId()->willReturn(42);
        $project->getOwner()->willReturn($owner);

        $this->vote($token, $project, [ProjectVoter::OWN])->shouldReturn(VoterInterface::ACCESS_DENIED);

        $contributor->getId()->willReturn(13);
        $token->getUser()->willReturn($contributor);

        $otherUser->getId()->willReturn(1337);
        $userRepository->findContributorToNotify($project)->willReturn([$otherUser]);

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
