<?php

namespace spec\PimEnterprise\Component\TeamWorkAssistant\Calculator;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\TeamWorkAssistant\Calculator\ContributorGroupCalculator;
use PimEnterprise\Component\TeamWorkAssistant\Calculator\ProjectItemCalculatorInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\AttributePermissionRepositoryInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\FamilyRequirementRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;

class ContributorGroupCalculatorSpec extends ObjectBehavior
{
    function let(
        CategoryAccessRepository $categoryAccessRepository,
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        AttributePermissionRepositoryInterface $attributePermissionRepository
    ) {
        $this->beConstructedWith(
            $categoryAccessRepository,
            $familyRequirementRepository,
            $attributePermissionRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContributorGroupCalculator::class);
    }

    function it_is_a_calculator()
    {
        $this->shouldImplement(ProjectItemCalculatorInterface::class);
    }

    function it_adds_to_the_project_the_user_group_that_have_edit_permission_on_categories_and_attribute_groups(
        $categoryAccessRepository,
        $familyRequirementRepository,
        $attributePermissionRepository,
        Group $userGroup,
        Group $otherUserGroup,
        ProductInterface $product,
        ProjectInterface $project,
        FamilyInterface $family,
        ChannelInterface $channel
    ) {
        $categoryAccessRepository->getGrantedUserGroupsForProduct($product, Attributes::EDIT_ITEMS)
            ->willreturn(
                [
                    ['name' => 'Redactor'],
                    ['name' => 'Catalog manager'],
                ]
            );

        $product->getFamily()->willreturn($family);
        $project->getChannel()->willreturn($channel);

        $familyRequirementRepository->findAttributeGroupIdentifiers($family, $channel)
            ->willReturn(['marketing', 'other']);

        $attributePermissionRepository->findContributorsUserGroups(['marketing', 'other'])
            ->willReturn([$otherUserGroup, $userGroup]);

        $userGroup->getName()->willReturn('Redactor');
        $otherUserGroup->getName()->willReturn('It support');

        $this->calculate($project, $product)->shouldReturn([$userGroup]);
    }
}
