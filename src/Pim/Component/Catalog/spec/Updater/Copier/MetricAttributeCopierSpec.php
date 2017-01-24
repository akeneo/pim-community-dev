<?php

namespace spec\Pim\Component\Catalog\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricAttributeCopierSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        NormalizerInterface $normalizer
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $normalizer,
            ['pim_catalog_metric'],
            ['pim_catalog_metric']
        );
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Copier\CopierInterface');
    }

    function it_supports_metric_attributes(
        AttributeInterface $fromMetricAttribute,
        AttributeInterface $toMetricAttribute,
        AttributeInterface $toTextareaAttribute,
        AttributeInterface $fromNumberAttribute,
        AttributeInterface $toNumberAttribute
    ) {
        $fromMetricAttribute->getAttributeType()->willReturn('pim_catalog_metric');
        $toMetricAttribute->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supportsAttributes($fromMetricAttribute, $toMetricAttribute)->shouldReturn(true);

        $fromNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supportsAttributes($fromNumberAttribute, $toNumberAttribute)->shouldReturn(false);

        $this->supportsAttributes($fromMetricAttribute, $toNumberAttribute)->shouldReturn(false);
        $this->supportsAttributes($fromNumberAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_a_metric_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValue $fromProductValue,
        ProductValue $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateUnitFamilies(Argument::cetera())->shouldBeCalled();

        $normalizer
            ->normalize($fromProductValue, 'standard')
            ->shouldBeCalled()
            ->willReturn(['amount' => 123, 'unit' => 'GRAM']);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $builder
            ->addProductValue($product1, $toAttribute, $toLocale, $toScope, ['amount' => 123, 'unit' => 'GRAM'])
            ->shouldBeCalled()
            ->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addProductValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldNotBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_throws_an_exception_when_unit_families_are_not_consistent(
        $attrValidatorHelper,
        ProductInterface $product,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute
    ) {
        $e = new \LogicException('Metric families are not the same for attributes: "fromCode" and "toCode".');
        $fromAttribute->getCode()->willReturn('fromCode');
        $toAttribute->getCode()->willReturn('toCode');
        $attrValidatorHelper->validateLocale(Argument::any(), Argument::any())->willReturn(null);
        $attrValidatorHelper->validateScope(Argument::any(), Argument::any())->willReturn(null);
        $attrValidatorHelper->validateUnitFamilies($fromAttribute, $toAttribute)->willThrow($e);

        $this->shouldThrow($e)->during('copyAttributeData', [$product, $product, $fromAttribute, $toAttribute]);
    }
}
