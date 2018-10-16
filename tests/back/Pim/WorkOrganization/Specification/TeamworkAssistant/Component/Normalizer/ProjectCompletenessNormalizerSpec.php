<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Normalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Normalizer\ProjectCompletenessNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProjectCompletenessNormalizerSpec extends ObjectBehavior
{
    function it_is_a_project_completeness_normalizer()
    {
        $this->shouldHaveType(ProjectCompletenessNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_project_completeness(ProjectCompleteness $projectCompleteness)
    {
        $projectCompleteness->isComplete()->willReturn(true);
        $projectCompleteness->getProductsCountTodo()->willReturn(0);
        $projectCompleteness->getProductsCountInProgress()->willReturn(0);
        $projectCompleteness->getProductsCountDone()->willReturn(1);
        $projectCompleteness->getRatioForTodo()->willReturn(0);
        $projectCompleteness->getRatioForInProgress()->willReturn(0);
        $projectCompleteness->getRatioForDone()->willReturn(100);

        $this->normalize($projectCompleteness, 'internal_api')->shouldReturn([
            'is_complete' => true,
            'products_count_todo' => 0,
            'products_count_in_progress' => 0,
            'products_count_done' => 1,
            'ratio_todo' => 0,
            'ratio_in_progress' => 0,
            'ratio_done' => 100,
        ]);
    }

    function it_specifies_that_the_normalizer_can_be_apply_on_a_project_with_the_internal_format(
        ProjectCompleteness $projectCompleteness,
        $object
    ) {
        $this->supportsNormalization($projectCompleteness, 'wrong_format')->shouldReturn(false);
        $this->supportsNormalization($object, 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($object, 'wrong_format')->shouldReturn(false);
        $this->supportsNormalization($projectCompleteness, 'internal_api')->shouldReturn(true);
    }
}
