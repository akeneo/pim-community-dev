<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    function it_supports_a_attribute(AttributeInterface $attribute)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_attribute($stdNormalizer, AttributeInterface $attribute)
    {
        $data = ['code' => 'my_attribute', 'labels' => []];

        $stdNormalizer->normalize($attribute, 'standard', [])->willReturn($data);

        $normalizedAttribute = $this->normalize($attribute, 'external_api', []);
        $normalizedAttribute->shouldHaveLabels($data);
    }

    public function getMatchers(): array
    {
        return [
            'haveLabels' => function ($subject) {
                return is_object($subject['labels']);
            }
        ];
    }
}
