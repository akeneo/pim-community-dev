<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer, NormalizerInterface $translationNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
        $this->beConstructedWith($stdNormalizer, $translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    function it_supports_an_attribute(AttributeInterface $attribute)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_an_attribute(NormalizerInterface $stdNormalizer, AttributeInterface $attribute)
    {
        $attribute->getGroup()->willReturn(null);

        $data = [
            'code' => 'my_attribute',
            'labels' => ['en_US' => 'english_label'],
        ];
        $stdNormalizer->normalize($attribute, 'standard', [])->shouldBeCalled()->willReturn($data);

        $this->normalize($attribute, 'external_api', [])->shouldBeLike(array_merge($data, ['group_labels' => null]));
    }

    function it_normalizes_an_attribute_with_its_group_labels(
        NormalizerInterface $stdNormalizer,
        NormalizerInterface $translationNormalizer,
        AttributeInterface $attribute,
        AttributeInterface $group
    ) {
        $attribute->getGroup()->willReturn($group);

        $data = [
            'code' => 'my_attribute',
            'labels' => ['en_US' => 'english_label'],
            'group' => 'attributeGroupA',
        ];
        $stdNormalizer->normalize($attribute, 'standard', [])->willReturn($data);

        $translationNormalizer->normalize($group, 'external_api', [])
            ->shouldBeCalled()
            ->willReturn(['en_US' => 'attribute group A']);

        $this->normalize($attribute, 'external_api', [])
            ->shouldBeLike(array_merge($data, ['group_labels' => ['en_US' => 'attribute group A']]));
    }

    function it_normalizes_an_attribute_with_empty_labels(
        NormalizerInterface $stdNormalizer,
        NormalizerInterface $translationNormalizer,
        AttributeInterface $attribute,
        AttributeInterface $group
    ) {
        $attribute->getGroup()->willReturn($group);

        $data = ['code' => 'my_attribute', 'labels' => [], 'group' => 'attributeGroupA'];
        $stdNormalizer->normalize($attribute, 'standard', [])->willReturn($data);

        $translationNormalizer->normalize($group, 'external_api', [])->shouldBeCalled()->willReturn([]);

        $this->normalize($attribute, 'external_api', [])->shouldBeLike(
            [
                'code' => 'my_attribute',
                'labels' => (object)[],
                'group' => 'attributeGroupA',
                'group_labels' => (object)[],
            ]
        );
    }
}
