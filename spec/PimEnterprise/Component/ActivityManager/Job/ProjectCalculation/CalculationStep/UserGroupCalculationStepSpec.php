<?php

namespace spec\PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\UserGroupCalculationStep;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\AttributePermissionRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\FamilyRequirementRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;

class UserGroupCalculationStepSpec extends ObjectBehavior
{
    function let(
        ObjectDetacherInterface $objectDetacher,
        CategoryAccessRepository $categoryAccessRepository,
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        AttributePermissionRepositoryInterface $attributePermissionRepository
    ) {
        $this->beConstructedWith(
            $objectDetacher,
            $categoryAccessRepository,
            $familyRequirementRepository,
            $attributePermissionRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserGroupCalculationStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
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
            ->willreturn([
                ['name' => 'Redactor'],
                ['name' => 'Catalog manager'],
            ]);

        $product->getFamily()->willreturn($family);
        $project->getChannel()->willreturn($channel);

        $familyRequirementRepository->findAttributeGroupIdentifiers($family, $channel)
            ->willReturn(['marketing', 'other']);

        $attributePermissionRepository->findContributorsUserGroups(['marketing', 'other'])
            ->willReturn([$otherUserGroup, $userGroup]);

        $userGroup->getName()->willReturn('Redactor');
        $otherUserGroup->getName()->willReturn('It support');

        $project->addUserGroup($userGroup)->shouldBeCalled();
        $project->addUserGroup($otherUserGroup)->shouldNotBeCalled();

        $this->execute($product, $project);
    }
}
