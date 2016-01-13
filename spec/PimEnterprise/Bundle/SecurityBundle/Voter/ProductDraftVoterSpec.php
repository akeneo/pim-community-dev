<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes as SecurityAttributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Security\Attributes as WorkflowAttributes;
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

    function it_supports_the_right_attributes()
    {
        $this->supportsAttribute(WorkflowAttributes::FULL_REVIEW)->shouldReturn(true);
        $this->supportsAttribute(WorkflowAttributes::PARTIAL_REVIEW)->shouldReturn(true);
        $this->supportsAttribute(SecurityAttributes::OWN)->shouldReturn(true);
    }

    function it_grants_full_review_access_if_the_user_can_edit_all_values_contained_in_the_draft(
        $attrGroupRepository,
        $attrGroupAccessManager,
        UserInterface $user,
        TokenInterface $token,
        ProductDraftInterface $draft,
        AttributeGroupInterface $group
    ) {
        $token->getUser()->willReturn($user);
        $draft->getChanges()->willReturn($this->getChanges(['description']));

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['description'])
            ->willReturn([$group]);

        $attrGroupAccessManager
            ->isUserGranted($user, $group, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->vote($token, $draft, [WorkflowAttributes::FULL_REVIEW])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_full_review_access_if_the_user_cannot_edit_one_of_the_values_contained_in_the_draft(
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

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['description', 'price'])
            ->willReturn([$groupGranted, $groupDenied]);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupGranted, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(true);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupDenied, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->vote($token, $draft, [WorkflowAttributes::FULL_REVIEW])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_grants_partial_review_access_if_the_user_can_edit_at_least_one_value_contained_in_the_draft(
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

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['description', 'price'])
            ->willReturn([$groupDenied, $groupGranted]);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupDenied, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(false);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupGranted, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->vote($token, $draft, [WorkflowAttributes::PARTIAL_REVIEW])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_all_review_accesses_if_the_user_can_edit_none_of_the_values_contained_in_the_draft(
        $attrGroupRepository,
        $attrGroupAccessManager,
        UserInterface $user,
        TokenInterface $token,
        ProductDraftInterface $draft,
        AttributeGroupInterface $groupDenied1,
        AttributeGroupInterface $groupDenied2
    ) {
        $token->getUser()->willReturn($user);
        $draft->getChanges()->willReturn($this->getChanges(['description', 'price']));

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['description', 'price'])
            ->willReturn([$groupDenied1, $groupDenied2]);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupDenied1, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(false);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupDenied2, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->vote($token, $draft, [WorkflowAttributes::FULL_REVIEW])->shouldReturn(VoterInterface::ACCESS_DENIED);
        $this->vote($token, $draft, [WorkflowAttributes::PARTIAL_REVIEW])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_abstains_if_class_is_not_supported(TokenInterface $token)
    {
        $this->vote($token, Argument::any(), [WorkflowAttributes::FULL_REVIEW])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_abstains_if_attribute_is_not_supported(TokenInterface $token, ProductDraftInterface $draft)
    {
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
        ProductDraftInterface $productDraft
    ) {
        $this->vote($token, $productDraft, ['SOMETHING'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_does_not_vote_if_checking_the_own_access_of_something_else_than_a_product_draft(
        TokenInterface $token,
        ProductInterface $product
    ) {
        $this->vote($token, $product, [SecurityAttributes::OWN])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
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
}
