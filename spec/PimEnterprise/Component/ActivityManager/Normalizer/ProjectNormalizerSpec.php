<?php

namespace spec\Akeneo\ActivityManager\Component\Normalizer;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Normalizer\ProjectNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProjectNormalizerSpec extends ObjectBehavior
{
    function it_is_a_project_normalizer()
    {
        $this->shouldHaveType(ProjectNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_project(ProjectInterface $project)
    {
        $project->getLabel()->willReturn('Summer collection');
        $this->normalize($project)->shouldReturn([
            'label' => 'Summer collection'
        ]);
    }

    function it_throws_an_exception_if_object_to_normalize_is_not_a_project($object)
    {
        $this->shouldThrow('\InvalidArgumentException')->during('normalize', [$object]);
    }

    function it_specifies_that_the_normalizer_can_be_apply_on_a_project_with_the_internal_format(
        ProjectInterface $project,
        $object
    ) {
        $this->supportsNormalization($project, 'wrong_format')->shouldReturn(false);
        $this->supportsNormalization($object, 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($object, 'wrong_format')->shouldReturn(false);
        $this->supportsNormalization($project, 'internal_api')->shouldReturn(true);
    }
}
