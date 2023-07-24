<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $stdNormalizer, NormalizerInterface $translationNormalizer): void
    {
        $this->beConstructedWith($stdNormalizer);
        $this->beConstructedWith($stdNormalizer, $translationNormalizer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    public function it_supports_an_attribute(AttributeInterface $attribute): void
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'external_api')->shouldReturn(true);
    }

    public function it_normalizes_an_attribute(
        NormalizerInterface $stdNormalizer,
        AttributeInterface $attribute
    ): void {
        $attribute->getGroup()->willReturn(null);
        $attribute->getType()->willReturn(null);

        $data = [
            'code' => 'my_attribute',
            'labels' => ['en_US' => 'english_label'],
        ];
        $stdNormalizer->normalize($attribute, 'standard', [])->shouldBeCalled()->willReturn($data);

        $this->normalize($attribute, 'external_api', [])->shouldBeLike(array_merge($data, ['group_labels' => null]));
    }

    public function it_normalizes_an_attribute_with_its_group_labels(
        NormalizerInterface $stdNormalizer,
        NormalizerInterface $translationNormalizer,
        AttributeInterface $attribute,
        AttributeInterface $group
    ): void {
        $attribute->getGroup()->willReturn($group);
        $attribute->getType()->willReturn(null);

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

    public function it_normalizes_an_attribute_with_empty_labels(
        NormalizerInterface $stdNormalizer,
        NormalizerInterface $translationNormalizer,
        AttributeInterface $attribute,
        AttributeInterface $group
    ): void {
        $attribute->getGroup()->willReturn($group);
        $attribute->getType()->willReturn(null);

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

    public function it_normalizes_identifier_attribute(
        NormalizerInterface $stdNormalizer,
        AttributeInterface $attribute
    ): void {
        $attribute->getGroup()->willReturn(null);
        $attribute->getType()->willReturn(AttributeTypes::IDENTIFIER);
        $attribute->isMainIdentifier()->shouldBeCalled()->willReturn(true);

        $data = [
            'code' => 'my_identifier_attribute',
            'labels' => ['en_US' => 'english_label'],
        ];
        $stdNormalizer->normalize($attribute, 'standard', [])->shouldBeCalled()->willReturn($data);

        $dataNormalizedexpected = array_merge($data, ['group_labels' => null, 'is_main_identifier' => true]);
        $this->normalize($attribute, 'external_api', [])->shouldBeLike($dataNormalizedexpected);
    }
}
