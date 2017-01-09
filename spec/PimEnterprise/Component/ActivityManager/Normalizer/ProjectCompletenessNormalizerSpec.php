<?php

namespace spec\PimEnterprise\Component\ActivityManager\Normalizer;

use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Normalizer\ProjectCompletenessNormalizer;
use PhpSpec\ObjectBehavior;
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
            'isComplete' => true,
            'productsCountTodo' => 0,
            'productsCountInProgress' => 0,
            'productsCountDone' => 1,
            'ratioTodo' => 0,
            'ratioInProgress' => 0,
            'ratioDone' => 100,
        ]);
    }

    function it_throws_an_exception_if_object_to_normalize_is_not_a_project_completeness($object)
    {
        $this->shouldThrow('\InvalidArgumentException')->during('normalize', [$object]);
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
