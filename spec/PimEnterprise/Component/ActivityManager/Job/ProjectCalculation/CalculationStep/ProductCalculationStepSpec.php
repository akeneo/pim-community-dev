<?php

namespace spec\Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep\ProductCalculationStep;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
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
