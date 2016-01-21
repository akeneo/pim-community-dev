<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
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
        LocaleRepositoryInterface $localeRepository,
        AttributeGroupAccessManager $attrGroupAccessManager,
        VoterInterface $localeVoter
    ) {
        $this->beConstructedWith($attrGroupRepository, $localeRepository, $attrGroupAccessManager, $localeVoter);
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
        $this->supportsClass(new \stdClass())->shouldReturn(false);
    }

    function it_supports_the_right_attributes()
    {
        $this->supportsAttribute(WorkflowAttributes::FULL_REVIEW)->shouldReturn(true);
        $this->supportsAttribute(WorkflowAttributes::PARTIAL_REVIEW)->shouldReturn(true);
        $this->supportsAttribute(SecurityAttributes::OWN)->shouldReturn(true);
        $this->supportsAttribute('not_supported')->shouldReturn(false);
    }

    function it_grants_full_review_access_if_the_user_can_edit_all_values_contained_in_the_draft(
        $attrGroupRepository,
        $localeRepository,
        $attrGroupAccessManager,
        $localeVoter,
        UserInterface $user,
        TokenInterface $token,
        ProductDraftInterface $draft,
        AttributeGroupInterface $group,
        LocaleInterface $enLocale
    ) {
        $token->getUser()->willReturn($user);
        $draft->isInProgress()->willReturn(false);
        $draft->getChangesToReview()->willReturn([
            'values' => [
                'description' => [['locale' => 'en_US', 'scope' => null, 'data' => '']],
            ]
        ]);

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['description'])
            ->willReturn([$group]);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);

        $attrGroupAccessManager
            ->isUserGranted($user, $group, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(true);

        $localeVoter
            ->vote($token, $enLocale, [SecurityAttributes::EDIT_ITEMS])
            ->shouldBeCalled()
            ->willReturn(VoterInterface::ACCESS_GRANTED);

        $this->vote($token, $draft, [WorkflowAttributes::FULL_REVIEW])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_full_review_access_if_the_user_cannot_edit_one_of_the_values_contained_in_the_draft(
        $attrGroupRepository,
        $localeRepository,
        $attrGroupAccessManager,
        $localeVoter,
        UserInterface $user,
        TokenInterface $token,
        ProductDraftInterface $draft,
        AttributeGroupInterface $groupGranted,
        AttributeGroupInterface $groupDenied,
        LocaleInterface $enLocale
    ) {
        $token->getUser()->willReturn($user);
        $draft->isInProgress()->willReturn(false);
        $draft->getChangesToReview()->willReturn([
            'values' => [
                'description' => [['locale' => 'en_US', 'scope' => null, 'data' => '']],
                'price'       => [['locale' => null, 'scope' => null, 'data' => '']],
            ]
        ]);

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['description'])
            ->willReturn([$groupGranted]);

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['price'])
            ->willReturn([$groupDenied]);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupGranted, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(true);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupDenied, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(false);

        $localeVoter
            ->vote($token, $enLocale, [SecurityAttributes::EDIT_ITEMS])
            ->shouldBeCalled()
            ->willReturn(VoterInterface::ACCESS_GRANTED);

        $this->vote($token, $draft, [WorkflowAttributes::FULL_REVIEW])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_grants_partial_review_access_if_the_user_can_edit_at_least_one_value_contained_in_the_draft(
        $attrGroupRepository,
        $localeRepository,
        $attrGroupAccessManager,
        $localeVoter,
        UserInterface $user,
        TokenInterface $token,
        ProductDraftInterface $draft,
        AttributeGroupInterface $groupGranted,
        AttributeGroupInterface $groupDenied,
        LocaleInterface $enLocale
    ) {
        $token->getUser()->willReturn($user);
        $draft->isInProgress()->willReturn(false);
        $draft->getChangesToReview()->willReturn([
            'values' => [
                'description' => [['locale' => 'en_US', 'scope' => null, 'data' => '']],
                'price'       => [['locale' => null, 'scope' => null, 'data' => '']],
            ]
        ]);

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['description'])
            ->willReturn([$groupGranted]);

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['price'])
            ->willReturn([$groupDenied]);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);

        $attrGroupAccessManager
            ->isUserGranted($user, $groupGranted, SecurityAttributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn(true);

        $localeVoter
            ->vote($token, $enLocale, [SecurityAttributes::EDIT_ITEMS])
            ->shouldBeCalled()
            ->willReturn(VoterInterface::ACCESS_GRANTED);

        $this->vote($token, $draft, [WorkflowAttributes::PARTIAL_REVIEW])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_all_review_accesses_if_the_user_can_edit_none_of_the_values_contained_in_the_draft(
        $attrGroupRepository,
        $localeRepository,
        $attrGroupAccessManager,
        UserInterface $user,
        TokenInterface $token,
        ProductDraftInterface $draft,
        AttributeGroupInterface $groupDenied1,
        AttributeGroupInterface $groupDenied2,
        LocaleInterface $enLocale
    ) {
        $token->getUser()->willReturn($user);
        $draft->isInProgress()->willReturn(false);
        $draft->getChangesToReview()->willReturn([
            'values' => [
                'description' => [['locale' => 'en_US', 'scope' => null, 'data' => '']],
                'price'       => [['locale' => null, 'scope' => null, 'data' => '']],
            ]
        ]);

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['description'])
            ->willReturn([$groupDenied1]);

        $attrGroupRepository
            ->getAttributeGroupsFromAttributeCodes(['price'])
            ->willReturn([$groupDenied2]);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);

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
}
