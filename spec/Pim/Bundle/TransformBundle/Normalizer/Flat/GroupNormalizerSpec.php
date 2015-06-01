<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\TransformBundle\Normalizer\Flat\TranslationNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(
        TranslationNormalizer $transNormalizer,
        DenormalizerInterface $valuesDenormalizer,
        GroupInterface $group
    ) {
        $this->beConstructedWith($transNormalizer, $valuesDenormalizer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_only_supports_csv_normalization_of_group($group)
    {
        $this->supportsNormalization($group, 'csv')->shouldReturn(true);
        $this->supportsNormalization($group, 'flat')->shouldReturn(false);
        $this->supportsNormalization($group, 'json')->shouldReturn(false);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_group_without_axis_attribute(
        $transNormalizer,
        $group,
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
        $group,
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
            'axis' => 'is_alliance_related,is_empire_related'
        ]);
    }

    function it_normalizes_a_variant_group_and_sorts_axis_attributes(
        $transNormalizer,
        $group,
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
            'axis' => 'color,horses,is_magic'
        ]);
    }

    function it_normalizes_a_variant_group_with_its_values(
        $transNormalizer,
        $valuesDenormalizer,
        $group,
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
        $valuesDenormalizer->denormalize($valuesData, 'ProductValue[]', 'json')->willReturn([
            $productValue1,
            $productValue2
        ]);

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
        $this->normalize($group, $format, $context)->shouldReturn([
            'code' => 'laser_sabers',
            'type' => 'VARIANT',
            'axis' => 'light_color',
            'name' => 'Light saber model',
            'size' => '120'
        ]);
    }

    function it_normalizes_a_variant_group_with_its_values_on_versioning_context(
        $transNormalizer,
        $valuesDenormalizer,
        $group,
        CustomSerializer $serializer,
        GroupTypeInterface $groupType,
        AttributeInterface $attr,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue1
    ) {
        $groupType->getCode()->willReturn('VARIANT');
        $groupType->isVariant()->willReturn(true);
        $group->getCode()->willReturn('lego');

        $valuesData = [
            'name' => 'Light saber model',
            'size' => '120'
        ];

        $context = ['versioning' => true];
        $format = 'csv';

        $productTemplate->getValuesData()->willReturn($valuesData);
        $valuesDenormalizer->denormalize($valuesData, 'ProductValue[]', 'json')->willReturn([
            $productValue1
        ]);

        $newContext = array_merge($context, ['with_variant_group_values' => true]);
        $serializer->normalize($productValue1, $format, ['entity' => 'product'] + $newContext)
            ->willReturn(['age' => '6+']);

        $group->getProductTemplate()->willReturn($productTemplate);
        $group->getType()->willReturn($groupType);

        $attr->getCode()->willReturn('model');

        $group->getAxisAttributes()->willReturn([$attr]);

        $transNormalizer->normalize($group, $format, $context)->willReturn([]);

        $this->setSerializer($serializer);
        $this->normalize($group, $format, $context)->shouldReturn([
            'code' => 'lego',
            'type' => 'VARIANT',
            'axis' => 'model',
            'age' => '6+'
        ]);
    }
}

abstract class CustomSerializer implements SerializerInterface
{
    public function normalize($value, $format, $context)
    {
    }
}
