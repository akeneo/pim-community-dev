<?php

namespace spec\Pim\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Normalizer\FamilyNormalizer;
use Pim\Component\Catalog\Model\FamilyInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyNormalizer::class);
    }

    function it_supports_a_family(FamilyInterface $family)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($family, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($family, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_family($stdNormalizer, FamilyInterface $family)
    {
        $data = ['code' => 'my_family', 'labels' => [], 'attribute_requirements' => []];

        $stdNormalizer->normalize($family, 'standard', [])->willReturn($data);

        $normalizedFamily = $this->normalize($family, 'external_api', []);
        $normalizedFamily->shouldHaveLabels($data);
        $normalizedFamily->shouldHaveAttributeRequirements($data);
    }

    public function getMatchers()
    {
        return [
            'haveLabels' => function ($subject) {
                return is_object($subject['labels']);
            },
            'haveAttributeRequirements' => function ($subject) {
                return is_object($subject['attribute_requirements']);
            }
        ];
    }
}
