<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Security;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Security\ProductDraftVoter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes as SecurityAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductDraftVoterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductDraftVoter::class);
    }

    function it_is_a_voter()
    {
        $this->shouldHaveType(VoterInterface::class);
    }

    function it_abstains_if_class_is_not_supported(TokenInterface $token)
    {
        $this->vote($token, Argument::any(), [SecurityAttributes::OWN])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_abstains_if_attribute_is_not_supported(
        TokenInterface $token,
        EntityWithValuesDraftInterface $draft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $this->vote($token, $draft, ['foo'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_grants_own_access_to_user_that_has_created_the_draft(
        TokenInterface $token,
        EntityWithValuesDraftInterface $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUserIdentifier()->willReturn('bob');
        $productDraft->getAuthor()->willReturn('bob');

        $this->vote($token, $productDraft, [SecurityAttributes::OWN])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_own_access_to_user_that_has_not_created_the_draft(
        TokenInterface $token,
        EntityWithValuesDraftInterface $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUserIdentifier()->willReturn('bob');
        $productDraft->getAuthor()->willReturn('alice');

        $this->vote($token, $productDraft, [SecurityAttributes::OWN])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_the_attribute_own_is_not_being_checked(
        TokenInterface $token,
        EntityWithValuesDraftInterface $productDraft,
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
