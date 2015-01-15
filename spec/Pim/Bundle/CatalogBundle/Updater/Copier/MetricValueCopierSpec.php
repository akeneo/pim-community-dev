<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MetricValueCopierSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        MetricFactory $metricFactory
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $metricFactory,
            ['pim_catalog_metric'],
            ['pim_catalog_metric']
        );
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface');
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
        $this->supports($fromMetricAttribute, $toMetricAttribute)->shouldReturn(true);

        $fromNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supports($fromNumberAttribute, $toNumberAttribute)->shouldReturn(false);

        $this->supports($fromMetricAttribute, $toNumberAttribute)->shouldReturn(false);
        $this->supports($fromNumberAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_a_metric_value_to_a_product_value(
        $builder,
        $metricFactory,
        $attrValidatorHelper,
        MetricInterface $metric,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        ProductValue $fromProductValue,
        ProductValue $toProductValue,
        ProductValue $toProductValue2
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

        $fromProductValue->getData()->willReturn($metric);
        $toProductValue->setMetric($metric)->shouldBeCalledTimes(2);
        $toProductValue->getData()->willReturn($metric);
        $toProductValue->getMetric()->willReturn($metric);

        $toProductValue2->setMetric($metric)->shouldBeCalledTimes(1);
        $toProductValue2->getData()->willReturn($metric);
        $toProductValue2->getMetric()->willReturn(null);

        $metric->getFamily()->shouldBeCalled()->willReturn('Weight');
        $metric->getData()->shouldBeCalled()->willReturn(123);
        $metric->getUnit()->shouldBeCalled()->willReturn('kg');

        $metric->setData(123)->shouldBeCalled();
        $metric->setUnit('kg')->shouldBeCalled();

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $product4->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product4->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue2);

        $metricFactory->createMetric('Weight')->shouldBeCalledTimes(1)->willReturn($metric);

        $builder->addProductValue($product3, $toAttribute, $toLocale, $toScope)->shouldBeCalledTimes(1)->willReturn($toProductValue);

        $products = [$product1, $product2, $product3, $product4];

        $this->copyValue($products, $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);
    }
}
