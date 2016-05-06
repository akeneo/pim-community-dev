<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Normalizer\Structured\TranslationNormalizer;
use Prophecy\Argument;
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

    function it_supports_json_and_xml_normalization_of_group(GroupInterface $group)
    {
        $this->supportsNormalization($group, 'json')->shouldReturn(true);
        $this->supportsNormalization($group, 'xml')->shouldReturn(true);
        $this->supportsNormalization($group, 'csv')->shouldReturn(false);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_group_without_axis_attribute(
        $transNormalizer,
        GroupInterface $group,
        GroupTypeInterface $groupType
    ) {
        $groupType->getCode()->willReturn('RELATED');
        $group->getCode()->willReturn('halloween_group');
        $group->getType()->willReturn($groupType);

        $group->getAxisAttributes()->willReturn([]);

        $transNormalizer->normalize($group, null, [])->willReturn([]);

        $this->normalize($group)->shouldReturn([
            'code' => 'halloween_group',
            'type' => 'RELATED'
        ]);
    }

    function it_normalizes_a_group_with_axis_attributes(
        $transNormalizer,
        GroupInterface $group,
        GroupTypeInterface $groupType,
        AttributeInterface $attr1,
        AttributeInterface $attr2
    ) {
        $groupType->getCode()->willReturn('RELATED');
        $group->getCode()->willReturn('starwars_clothes');
        $group->getType()->willReturn($groupType);

        $attr1->getCode()->willReturn('is_alliance_related');
        $attr2->getCode()->willReturn('is_empire_related');

        $group->getAxisAttributes()->willReturn([$attr1, $attr2]);

        $transNormalizer->normalize($group, null, [])->willReturn([]);

        $this->normalize($group)->shouldReturn([
            'code' => 'starwars_clothes',
            'type' => 'RELATED',
            'axis' => ['is_alliance_related', 'is_empire_related']
        ]);
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

        $this->normalize($group)->shouldReturn([
            'code' => 'lotr_clothes',
            'type' => 'VARIANT',
            'axis' => ['color', 'horses', 'is_magic']
        ]);
    }

    function it_normalizes_a_variant_group_with_its_values(
        $transNormalizer,
        $valuesDenormalizer,
        $valuesNormalizer,
        GroupInterface $group,
        GroupTypeInterface $groupType,
        AttributeInterface $attr,
        ProductTemplateInterface $productTemplate
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
        $valuesDenormalizer->denormalize($valuesData, Argument::any(), Argument::any())->willReturn('denormalized_values');
        $valuesNormalizer->normalize('denormalized_values', Argument::any(), Argument::any())->willReturn('normalized_values');

        $group->getProductTemplate()->willReturn($productTemplate);
        $group->getType()->willReturn($groupType);

        $attr->getCode()->willReturn('light_color');

        $group->getAxisAttributes()->willReturn([$attr]);

        $transNormalizer->normalize($group, $format, $context)->willReturn([]);

        $this->normalize($group, $format, $context)->shouldReturn([
            'code' => 'laser_sabers',
            'type' => 'VARIANT',
            'axis' => ['light_color'],
            'values' => 'normalized_values'
        ]);
    }
}
