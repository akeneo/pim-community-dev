<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\ProjectItemCalculatorInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\ProcessAttributeGroupCompletenessStep;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\AttributeGroupCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\PreProcessingRepositoryInterface;

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
        ProjectInterface $project,
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $preProcessingRepository->isProcessableAttributeGroupCompleteness($product, $project)->willReturn(true);

        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);

        $attributeGroupCompleteness1 = new AttributeGroupCompleteness(40, 0, 1);
        $attributeGroupCompleteness2 = new AttributeGroupCompleteness(33, 0, 1);
        $attributeGroupCompletenessCalculator->calculate($product, $channel, $locale)->willReturn(
            [$attributeGroupCompleteness1, $attributeGroupCompleteness2]
        );

        $preProcessingRepository->addAttributeGroupCompleteness($product, $channel, $locale, [
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
