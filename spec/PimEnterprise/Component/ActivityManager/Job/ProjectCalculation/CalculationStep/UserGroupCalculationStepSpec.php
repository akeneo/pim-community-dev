<?php

namespace spec\Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep\UserGroupCalculationStep;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;

class UserGroupCalculationStepSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $projectUpdater,
        ObjectDetacherInterface $objectDetacher,
        CategoryAccessRepository $categoryAccessRepository,
        AttributeGroupAccessRepository $attributeGroupAccessRepository
    ) {
        $this->beConstructedWith(
            $projectUpdater,
            $objectDetacher,
            $categoryAccessRepository,
            $attributeGroupAccessRepository
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

    function it_calculates_the_project_user_groups(
        $projectUpdater,
        Group $userGroup,
        ProductInterface $product,
        ProjectInterface $project
    ) {
        // TODO find $userGroup

        $projectUpdater->update($userGroup, [
            'user_groups' => [$userGroup]
        ])->shouldBeCalled();

        $this->execute($product, $project);
    }
}
