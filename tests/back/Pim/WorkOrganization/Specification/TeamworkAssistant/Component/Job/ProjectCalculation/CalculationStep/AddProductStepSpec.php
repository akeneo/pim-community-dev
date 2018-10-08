<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\AddProductStep;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\PreProcessingRepositoryInterface;

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
