<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VariantGroupNormalizerSpec extends ObjectBehavior
{
    function let(
        TranslationNormalizer $transNormalizer,
        NormalizerInterface $valuesNormalizer
    ) {
        $this->beConstructedWith($transNormalizer, $valuesNormalizer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\VariantGroupNormalizer');
    }

    function it_only_supports_csv_and_flat_normalization_of_group(GroupInterface $group, GroupTypeInterface $groupType)
    {
        $group->getType()->willReturn($groupType);
        $groupType->isVariant()->willReturn(true, true, true);
        $this->supportsNormalization($group, 'csv')->shouldReturn(false);
        $this->supportsNormalization($group, 'flat')->shouldReturn(true);
        $this->supportsNormalization($group, 'json')->shouldReturn(false);
    }

    function it_does_not_support_groups(GroupInterface $group, GroupTypeInterface $groupType)
    {
        $group->getType()->willReturn($groupType);
        $groupType->isVariant()->willReturn(false);
        $this->supportsNormalization($group, 'flat')->shouldReturn(false);
    }

    function it_does_not_support_flat_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'flat')->shouldBe(false);
    }

    function it_normalizes_a_variant_group_and_sorts_axis_attributes(
        $transNormalizer,
        $valuesNormalizer,
        GroupInterface $group,
        GroupTypeInterface $groupType,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue,
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

        $group->getProductTemplate()->willReturn($productTemplate);

        $productTemplate->getValues()->willReturn([$productValue]);
        $valuesNormalizer->normalize($productValue, 'flat', [])->willReturn(['name' => 'Light saber model']);

        $group->getAxisAttributes()->willReturn([$attr1, $attr2, $attr3]);

        $transNormalizer->normalize($group, 'standard', [])->willReturn(['en_US' => 'foo']);

        $this->normalize($group)->shouldReturn(
            [
                'code'        => 'lotr_clothes',
                'type'        => 'VARIANT',
                'axis'        => 'color,horses,is_magic',
                'name'        => 'Light saber model',
                'label-en_US' => 'foo',
            ]
        );
    }

    function it_normalizes_a_variant_group_with_its_values(
        $transNormalizer,
        $valuesNormalizer,
        GroupInterface $group,
        GroupTypeInterface $groupType,
        AttributeInterface $attr,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue1,
        ProductValueInterface $productValue2
    ) {
        $groupType->getCode()->willReturn('VARIANT');
        $groupType->isVariant()->willReturn(true);
        $group->getCode()->willReturn('laser_sabers');

        $context = ['with_variant_group_values' => true];
        $format = 'flat';

        $productTemplate->getValues()->willReturn(
            [
                $productValue1,
                $productValue2
            ]
        );

        $valuesNormalizer->normalize($productValue1, $format, [])->willReturn(['name' => 'Light saber model']);
        $valuesNormalizer->normalize($productValue2, $format, [])->willReturn(['size' => '120']);

        $group->getProductTemplate()->willReturn($productTemplate);
        $group->getType()->willReturn($groupType);

        $attr->getCode()->willReturn('light_color');

        $group->getAxisAttributes()->willReturn([$attr]);

        $transNormalizer->normalize($group, 'standard', $context)->willReturn([]);

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
