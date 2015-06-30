<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductDraftVoterSpec extends ObjectBehavior
{
    function let(
        AttributeGroupRepositoryInterface $attrGroupRepository,
        AttributeGroupAccessManager $attrGroupAccessManager
    ) {
        $this->beConstructedWith($attrGroupRepository, $attrGroupAccessManager);
    }

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
        $this->supportsClass(Argument::any())->shouldReturn(false);
    }

    function it_supports_edit_attributes()
    {
        $this->supportsAttribute(Attributes::EDIT_ATTRIBUTES)->shouldReturn(true);
    }

    function it_supports_the_own_attribute()
    {
        $this->supportsAttribute(Attributes::OWN)->shouldReturn(true);
    }

    function it_grants_voting_as_the_user_can_edit_all_values_contained_in_the_draft(
        $attrGroupRepository,
        $attrGroupAccessManager,
        UserInterface $user,
        TokenInterface $token,
        ProductDraftInterface $draft,
        AttributeGroupInterface $group
    ) {
        $token->getUser()->willReturn($user);
        $draft->getChanges()->willReturn($this->getChanges(['description']));

        $attrGroupRepository->getAttributeGroupsFromAttributeCodes(['description'])
            ->shouldBeCalled()->willReturn([$group]);

        $attrGroupAccessManager->isUserGranted($user, $group, Attributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()->willReturn(true);

        $this->vote($token, $draft, [Attributes::EDIT_ATTRIBUTES])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_voting_as_the_user_can_not_edit_all_values_contained_in_the_draft(
        $attrGroupRepository,
        $attrGroupAccessManager,
        UserInterface $user,
        TokenInterface $token,
        ProductDraftInterface $draft,
        AttributeGroupInterface $groupGranted,
        AttributeGroupInterface $groupDenied
    ) {
        $token->getUser()->willReturn($user);
        $draft->getChanges()->willReturn($this->getChanges(['description', 'price']));

        $attrGroupRepository->getAttributeGroupsFromAttributeCodes(['description', 'price'])
            ->shouldBeCalled()->willReturn([$groupGranted, $groupDenied]);

        $attrGroupAccessManager->isUserGranted($user, $groupGranted, Attributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()->willReturn(true);
        $attrGroupAccessManager->isUserGranted($user, $groupDenied, Attributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()->willReturn(false);

        $this->vote($token, $draft, [Attributes::EDIT_ATTRIBUTES])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_abstains_if_class_is_not_supported(TokenInterface $token)
    {
        $this->vote($token, Argument::any(), [Attributes::EDIT_ATTRIBUTES])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_abstains_if_attribute_is_not_supported(TokenInterface $token, ProductDraftInterface $draft)
    {
        $this->vote($token, $draft, ['foo'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_grants_own_access_to_user_that_has_created_the_proposal(
        TokenInterface $token,
        ProductDraftInterface $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('bob');
        $productDraft->getAuthor()->willReturn('bob');

        $this->vote($token, $productDraft, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_OWN_access_to_user_that_is_not_the_author_of_the_product_draft(
        TokenInterface $token,
        ProductDraftInterface $productDraft,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('bob');
        $productDraft->getAuthor()->willReturn('alice');

        $this->vote($token, $productDraft, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_the_attribute_own_is_not_being_checked(
        TokenInterface $token,
        ProductDraftInterface $productDraft
    ) {
        $this->vote($token, $productDraft, ['SOMETHING'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_does_not_vote_if_checking_the_own_access_of_something_else_than_a_product_draft(
        TokenInterface $token,
        ProductInterface $product
    ) {
        $this->vote($token, $product, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    private function getChanges(array $attributeCodes)
    {
        $changes = [];
        foreach ($attributeCodes as $code) {
            $changes[$code] = ['value' => ''];
        }

        return [
            'values' => $changes
        ];
    }

    private function getSingleChange($attributeCode)
    {
        return $attributeCode;
    }
}
