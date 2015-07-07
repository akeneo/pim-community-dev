<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
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
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\Voter\CategoryOwnerVoter');
    }

    function it_is_a_security_voter()
    {
        $this->shouldImplement('Symfony\Component\Security\Core\Authorization\Voter\VoterInterface');
    }

    function it_supports_acl_for_being_an_owner_of_at_least_one_category()
    {
        $this->supportsAttribute('foo')->shouldReturn(false);
        $this->supportsAttribute(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->shouldReturn(true);
    }

    function it_supports_any_class()
    {
        $this->supportsClass('foo')->shouldReturn(true);
        $this->supportsClass(new \stdClass())->shouldReturn(true);
    }

    function it_grants_access_if_the_current_user_is_the_owner_of_at_least_one_category(
        TokenInterface $token,
        User $user,
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
        User $user,
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
