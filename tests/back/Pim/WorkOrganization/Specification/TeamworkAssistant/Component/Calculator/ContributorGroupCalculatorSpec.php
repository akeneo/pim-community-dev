<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator;

use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\ContributorGroupCalculator;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\ProjectItemCalculatorInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\AttributePermissionRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;

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
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $categoryAccessRepository->getGrantedUserGroupsForEntityWithValues($product, Attributes::EDIT_ITEMS)
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

        $this->calculate($product, $channel, $locale)->shouldReturn([$userGroup]);
    }
}
