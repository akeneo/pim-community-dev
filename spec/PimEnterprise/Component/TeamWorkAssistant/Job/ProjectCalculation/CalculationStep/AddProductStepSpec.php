<?php

namespace spec\PimEnterprise\Component\TeamWorkAssistant\Job\ProjectCalculation\CalculationStep;

use PimEnterprise\Component\TeamWorkAssistant\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\TeamWorkAssistant\Job\ProjectCalculation\CalculationStep\AddProductStep;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\PreProcessingRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

class AddProductStepSpec extends ObjectBehavior
{
    function let(PreProcessingRepositoryInterface $preProcessingRepository)
    {
        $this->beConstructedWith($preProcessingRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddProductStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_adds_product_to_the_project(
        $preProcessingRepository,
        ProjectInterface $project,
        ProductInterface $product
    ) {
        $preProcessingRepository->addProduct($project, $product)->shouldBeCalled();

        $this->execute($product, $project);
    }
}
