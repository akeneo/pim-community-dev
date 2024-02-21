<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeGroupNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupNormalizer::class);
    }

    function it_supports_an_attribute_group(AttributeGroupInterface $attributeGroup)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($attributeGroup, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($attributeGroup, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_an_attribute_group($stdNormalizer, AttributeGroupInterface $attributeGroup)
    {
        $data = ['code' => 'my_attribute_group', 'labels' => []];

        $stdNormalizer->normalize($attributeGroup, 'standard', [])->willReturn($data);

        $normalizedAttribute = $this->normalize($attributeGroup, 'external_api', []);
        $normalizedAttribute->shouldHaveLabels($data);
    }

    public function getMatchers(): array
    {
        return [
            'haveLabels' => function ($subject) {
                return is_object($subject['labels']);
            },
        ];
    }
}
