<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\TeamworkAssistant\Calculator\ProjectItemCalculatorInterface;
use PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep\PreProcessCompletenessStep;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;

class PreProcessCompletenessStepSpec extends ObjectBehavior
{
    function let(
        ProjectItemCalculatorInterface $attributeGroupCompletenessCalculator,
        PreProcessingRepositoryInterface $preProcessingRepository
    ) {
        $this->beConstructedWith($preProcessingRepository, $attributeGroupCompletenessCalculator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PreProcessCompletenessStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_pre_processes_completeness(
        $preProcessingRepository,
        $attributeGroupCompletenessCalculator,
        ProductInterface $product,
        ProjectInterface $project
    ) {
        $attributeGroupCompleteness1 = new AttributeGroupCompleteness(40, 0, 1);
        $attributeGroupCompleteness2 = new AttributeGroupCompleteness(33, 0, 1);
        $attributeGroupCompletenessCalculator->calculate($project, $product)->willReturn(
            [$attributeGroupCompleteness1, $attributeGroupCompleteness2]
        );

        $preProcessingRepository->addAttributeGroupCompleteness($product, $project, [
            $attributeGroupCompleteness1,
            $attributeGroupCompleteness2,
        ])->shouldBeCalled();

        $this->execute($product, $project)->shouldReturn(null);
    }
}
