<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class PropositionOwnershipVoterSpec extends ObjectBehavior
{
    function it_is_a_security_voter()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Security\Core\Authorization\Voter\VoterInterface');
    }

    function it_supports_the_OWNER_attribute()
    {
        $this->supportsAttribute(Attributes::OWNER)->shouldReturn(true);
    }

    function it_supports_proposition(Proposition $proposition)
    {
        $this->supportsClass($proposition)->shouldReturn(true);
    }

    function it_grants_OWNER_access_to_user_that_has_created_the_prospotion(
        TokenInterface $token,
        Proposition $proposition,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('bob');
        $proposition->getAuthor()->willReturn('bob');

        $this->vote($token, $proposition, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_OWNER_access_to_user_that_is_not_the_author_of_the_proposition(
        TokenInterface $token,
        Proposition $proposition,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('bob');
        $proposition->getAuthor()->willReturn('alice');

        $this->vote($token, $proposition, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_the_attribute_OWNER_is_not_being_checked(
        TokenInterface $token,
        Proposition $proposition
    ) {
        $this->vote($token, $proposition, ['SOMETHING'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_does_not_vote_if_checking_the_OWNER_access_of_something_else_than_a_proposition(
        TokenInterface $token,
        ProductInterface $product
    ) {
        $this->vote($token, $product, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
