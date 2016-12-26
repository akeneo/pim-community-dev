<?php

namespace spec\PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\ProductCalculationStep;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

class ProductCalculationStepSpec extends ObjectBehavior
{
    function let(ProjectRepositoryInterface $projectRepository)
    {
        $this->beConstructedWith($projectRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductCalculationStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_adds_product_to_the_project($projectRepository, ProjectInterface $project, ProductInterface $product)
    {
        $projectRepository->addProduct($project, $product)->shouldBeCalled();

        $this->execute($product, $project);
    }
}
