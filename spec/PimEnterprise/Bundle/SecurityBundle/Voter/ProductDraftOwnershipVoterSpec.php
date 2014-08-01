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

class ProductDraftOwnershipVoterSpec extends ObjectBehavior
{
    function it_is_a_security_voter()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Security\Core\Authorization\Voter\VoterInterface');
    }

    function it_supports_the_OWN_attribute()
    {
        $this->supportsAttribute(Attributes::OWN)->shouldReturn(true);
    }

    function it_supports_product_draft(Proposition $productDraft)
    {
        $this->supportsClass($productDraft)->shouldReturn(true);
    }

    function it_grants_OWN_access_to_user_that_has_created_the_prospotion(
        TokenInterface $token,
        Proposition $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('bob');
        $productDraft->getAuthor()->willReturn('bob');

        $this->vote($token, $productDraft, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_OWN_access_to_user_that_is_not_the_author_of_the_product_draft(
        TokenInterface $token,
        Proposition $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('bob');
        $productDraft->getAuthor()->willReturn('alice');

        $this->vote($token, $productDraft, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_the_attribute_OWN_is_not_being_checked(
        TokenInterface $token,
        Proposition $productDraft
    ) {
        $this->vote($token, $productDraft, ['SOMETHING'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_does_not_vote_if_checking_the_OWN_access_of_something_else_than_a_product_draft(
        TokenInterface $token,
        ProductInterface $product
    ) {
        $this->vote($token, $product, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
