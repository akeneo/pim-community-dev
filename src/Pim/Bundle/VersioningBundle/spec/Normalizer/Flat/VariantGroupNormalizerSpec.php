<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(
        TranslationNormalizer $transNormalizer,
        DenormalizerInterface $valuesDenormalizer,
        NormalizerInterface $valuesNormalizer
    ) {
        $this->beConstructedWith($transNormalizer, $valuesDenormalizer, $valuesNormalizer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_only_supports_csv_and_flat_normalization_of_group(GroupInterface $group)
    {
        $this->supportsNormalization($group, 'csv')->shouldReturn(false);
        $this->supportsNormalization($group, 'flat')->shouldReturn(true);
        $this->supportsNormalization($group, 'json')->shouldReturn(false);
    }

    function it_does_not_support_flat_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'flat')->shouldBe(false);
    }

    function it_normalizes_a_variant_group_and_sorts_axis_attributes(
        $transNormalizer,
        GroupInterface $group,
        GroupTypeInterface $groupType,
        AttributeInterface $attr1,
        AttributeInterface $attr2,
        AttributeInterface $attr3
    ) {
        $groupType->getCode()->willReturn('VARIANT');
        $group->getCode()->willReturn('lotr_clothes');
        $group->getType()->willReturn($groupType);

        $attr1->getCode()->willReturn('is_magic');
        $attr2->getCode()->willReturn('color');
        $attr3->getCode()->willReturn('horses');

        $group->getAxisAttributes()->willReturn([$attr1, $attr2, $attr3]);

        $transNormalizer->normalize($group, null, [])->willReturn([]);

        $this->normalize($group)->shouldReturn(
            [
                'code' => 'lotr_clothes',
                'type' => 'VARIANT',
                'axis' => 'color,horses,is_magic'
            ]
        );
    }

    function it_normalizes_a_variant_group_with_its_values(
        $transNormalizer,
        $valuesDenormalizer,
        GroupInterface $group,
        CustomSerializer $serializer,
        GroupTypeInterface $groupType,
        AttributeInterface $attr,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue1,
        ProductValueInterface $productValue2
    ) {
        $groupType->getCode()->willReturn('VARIANT');
        $groupType->isVariant()->willReturn(true);
        $group->getCode()->willReturn('laser_sabers');

        $valuesData = [
            'name' => 'Light saber model',
            'size' => '120'
        ];

        $context = ['with_variant_group_values' => true];
        $format = 'csv';

        $productTemplate->getValuesData()->willReturn($valuesData);
        $valuesDenormalizer->denormalize($valuesData, 'ProductValue[]', 'json')->willReturn(
            [
                $productValue1,
                $productValue2
            ]
        );

        $serializer->normalize($productValue1, $format, ['entity' => 'product'] + $context)
            ->willReturn(['name' => 'Light saber model']);
        $serializer->normalize($productValue2, $format, ['entity' => 'product'] + $context)
            ->willReturn(['size' => '120']);

        $group->getProductTemplate()->willReturn($productTemplate);
        $group->getType()->willReturn($groupType);

        $attr->getCode()->willReturn('light_color');

        $group->getAxisAttributes()->willReturn([$attr]);

        $transNormalizer->normalize($group, $format, $context)->willReturn([]);

        $this->setSerializer($serializer);
        $this->normalize($group, $format, $context)->shouldReturn(
            [
                'code' => 'laser_sabers',
                'type' => 'VARIANT',
                'axis' => 'light_color',
                'name' => 'Light saber model',
                'size' => '120'
            ]
        );
    }
}
