<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductDraftApprovalVoterSpec extends ObjectBehavior
{
    function let(
        AttributeGroupRepository $attrGroupRepository,
        AttributeGroupAccessManager $attrGroupAccessManager
    ) {
        $this->beConstructedWith($attrGroupRepository, $attrGroupAccessManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\Voter\ProductDraftApprovalVoter');
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
        $this->supportsAttribute(Argument::any())->shouldReturn(false);
    }

    function it_grants_voting_as_the_user_can_edit_all_values_contained_in_the_draft(
        $attrGroupRepository,
        $attrGroupAccessManager,
        UserInterface $user,
        TokenInterface $token,
        ProductDraft $draft,
        AttributeGroup $group
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
        ProductDraft $draft,
        AttributeGroup $groupGranted,
        AttributeGroup $groupDenied
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

    function it_abstains_if_attribute_is_not_supported(TokenInterface $token, ProductDraft $draft)
    {
        $this->vote($token, $draft, ['foo'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    private function getChanges(array $attributeCodes)
    {
        $changes = [];
        foreach ($attributeCodes as $code) {
            $changes[] = $this->getSingleChange($code);
        }

        return [
            'values' => $changes
        ];
    }

    private function getSingleChange($attributeCode)
    {
        return [
            '__context__' => ['attribute' => $attributeCode]
        ];
    }

}
