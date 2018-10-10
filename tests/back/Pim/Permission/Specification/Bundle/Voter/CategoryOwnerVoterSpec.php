<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Voter;

use Akeneo\Pim\Permission\Bundle\Voter\CategoryOwnerVoter;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CategoryOwnerVoterSpec extends ObjectBehavior
{
    function let(CategoryAccessRepository $accessRepository)
    {
        $this->beConstructedWith($accessRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryOwnerVoter::class);
    }

    function it_is_a_security_voter()
    {
        $this->shouldImplement(VoterInterface::class);
    }

    function it_grants_access_if_the_current_user_is_the_owner_of_at_least_one_category(
        TokenInterface $token,
        UserInterface $user,
        $accessRepository
    ) {
        $token->getUser()->willReturn($user);
        $accessRepository->isOwner($user)->willReturn(true);

        $this->vote(
            $token,
            'foo',
            [Attributes::OWN_AT_LEAST_ONE_CATEGORY]
        )->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_access_if_no_user_is_found(TokenInterface $token)
    {
        $this->vote(
            $token,
            'foo',
            [Attributes::OWN_AT_LEAST_ONE_CATEGORY]
        )->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_denies_access_if_the_user_does_not_own_any_categories(
        TokenInterface $token,
        UserInterface $user,
        $accessRepository
    ) {
        $token->getUser()->willReturn($user);
        $accessRepository->isOwner($user)->willReturn(false);

        $this->vote(
            $token,
            'foo',
            [Attributes::OWN_AT_LEAST_ONE_CATEGORY]
        )->shouldReturn(VoterInterface::ACCESS_DENIED);
    }
}
