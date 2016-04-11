<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes as SecurityAttributes;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductDraftVoterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\Voter\ProductDraftVoter');
    }

    function it_is_a_voter()
    {
        $this->shouldHaveType('Symfony\Component\Security\Core\Authorization\Voter\VoterInterface');
    }

    function it_supports_product_drafts()
    {
        $this->supportsClass(new ProductDraft())->shouldReturn(true);
        $this->supportsClass(new \stdClass())->shouldReturn(false);
    }

    function it_supports_the_right_attributes()
    {
        $this->supportsAttribute(SecurityAttributes::OWN)->shouldReturn(true);
        $this->supportsAttribute('not_supported')->shouldReturn(false);
    }

    function it_abstains_if_class_is_not_supported(TokenInterface $token)
    {
        $this->vote($token, Argument::any(), [SecurityAttributes::OWN])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_abstains_if_attribute_is_not_supported(
        TokenInterface $token,
        ProductDraftInterface $draft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $this->vote($token, $draft, ['foo'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_grants_own_access_to_user_that_has_created_the_draft(
        TokenInterface $token,
        ProductDraftInterface $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('bob');
        $productDraft->getAuthor()->willReturn('bob');

        $this->vote($token, $productDraft, [SecurityAttributes::OWN])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_own_access_to_user_that_has_not_created_the_draft(
        TokenInterface $token,
        ProductDraftInterface $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('bob');
        $productDraft->getAuthor()->willReturn('alice');

        $this->vote($token, $productDraft, [SecurityAttributes::OWN])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_the_attribute_own_is_not_being_checked(
        TokenInterface $token,
        ProductDraftInterface $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $this->vote($token, $productDraft, ['SOMETHING'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_does_not_vote_if_checking_the_own_access_of_something_else_than_a_product_draft(
        TokenInterface $token,
        ProductInterface $product,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $this->vote($token, $product, [SecurityAttributes::OWN])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
