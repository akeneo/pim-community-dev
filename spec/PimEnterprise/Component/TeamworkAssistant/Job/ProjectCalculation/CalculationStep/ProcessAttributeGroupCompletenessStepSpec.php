<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\TeamworkAssistant\Calculator\ProjectItemCalculatorInterface;
use PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep\ProcessAttributeGroupCompletenessStep;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\FamilyRequirementRepositoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;

class ProcessAttributeGroupCompletenessStepSpec extends ObjectBehavior
{
    function let(
        ProjectItemCalculatorInterface $attributeGroupCompletenessCalculator,
        PreProcessingRepositoryInterface $preProcessingRepository
    ) {
        $this->beConstructedWith($preProcessingRepository, $attributeGroupCompletenessCalculator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProcessAttributeGroupCompletenessStep::class);
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
        $preProcessingRepository->isProcessableAttributeGroupCompleteness($product, $project)->willReturn(true);

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

    function it_does_not_process_the_attribute_completeness_if_it_already_computed(
        $preProcessingRepository,
        $attributeGroupCompletenessCalculator,
        ProductInterface $product,
        ProjectInterface $project
    ) {
        $preProcessingRepository->isProcessableAttributeGroupCompleteness($product, $project)->willReturn(false);

        $attributeGroupCompletenessCalculator->calculate($project, $product)->shouldNotBeCalled();

        $this->execute($product, $project);
    }
}
