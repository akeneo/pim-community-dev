<?php

namespace spec\Pim\Component\Catalog\Factory\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValue\MetricProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\Metric;
use Pim\Component\Catalog\Model\ProductValue;

class MetricProductValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_metric');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MetricProductValueFactory::class);
    }

    function it_supports_metric_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_metric')->shouldReturn(true);
    }

    function it_creates_an_empty_metric_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            new Metric(null, null, null, null, null)
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('metric_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_metric_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            new Metric(null, null, null, null, null)
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('metric_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_metric_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            new Metric('foofamily', 'foounit', 42, 'foobaseunit', 42)
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('metric_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveMetric('42.0000 foounit');
    }

    function it_creates_a_localizable_and_scopable_metric_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            new Metric('foofamily', 'foounit', 42, 'foobaseunit', 42)
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('metric_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveMetric('42.0000 foounit');
    }

    public function getMatchers()
    {
        return [
            'haveAttribute' => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable' => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'    => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'    => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'   => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'       => function ($subject) {
                $metric = $subject->getData();

                return $metric instanceof Metric && '' === $metric->__toString();
            },
            'haveMetric'    => function ($subject, $expectedMetric) {
                $metric = $subject->getData();

                return $metric instanceof Metric && $expectedMetric === $metric->__toString();
            },
        ];
    }
}
