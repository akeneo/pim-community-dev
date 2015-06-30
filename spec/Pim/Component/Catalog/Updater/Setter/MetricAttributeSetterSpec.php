<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MetricAttributeSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        MetricFactory $factory,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $factory,
            ['pim_catalog_metric']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface');
    }

    function it_supports_metric_attributes(
        AttributeInterface $metrictAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $metrictAttribute->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supportsAttribute($metrictAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_an_attribute_data(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $metricValue,
        MetricInterface $metric
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();
        $attribute->getCode()->willReturn('weight');
        $attribute->getMetricFamily()->willReturn('Weight');

        $product->getValue('weight', 'fr_FR', 'mobile')->willReturn($metricValue);
        $metricValue->getMetric()->willReturn($metric);
        $metric->setUnit('KILOGRAM')->shouldBeCalled();
        $metric->setData('107')->shouldBeCalled();
        $metricValue->setMetric($metric)->shouldBeCalled();

        $data = ['data' => 107, 'unit' => 'KILOGRAM'];
        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_given_attribute_data_is_not_an_array(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'Not an array';

        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('attributeCode', 'setter', 'metric', gettype($data))
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_there_is_no_attribute_data_key_in_array(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['unit' => 'KILOGRAM'];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected(
                'attributeCode',
                'data',
                'setter',
                'metric',
                print_r($data, true)
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_there_is_no_unit_key_in_array(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['data' => 'data value'];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('attributeCode', 'unit', 'setter', 'metric',
                print_r($data, true))
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_sets_numeric_attribute_data_to_a_product_value(
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        $builder,
        $factory,
        MetricInterface $metric,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = ['data' => 107, 'unit' => 'KILOGRAM'];

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->getMetricFamily()->willReturn('Weight');

        $productValue->getMetric()->willReturn(null);
        $productValue->setMetric($metric)->shouldBeCalled();

        $metric->setUnit('KILOGRAM')->shouldBeCalled();
        $metric->setData($data['data'])->shouldBeCalled();

        $builder
            ->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $factory->createMetric('Weight')->shouldBeCalledTimes(3)->willReturn($metric);

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product3, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }

    function it_sets_non_numeric_attribute_data_to_a_product_value(
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        $builder,
        $factory,
        MetricInterface $metric,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = ['data' => 'foo', 'unit' => 'KILOGRAM'];

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->getMetricFamily()->willReturn('Weight');

        $productValue->getMetric()->willReturn(null);
        $productValue->setMetric($metric)->shouldBeCalled();

        $metric->setUnit('KILOGRAM')->shouldBeCalled();
        $metric->setData($data['data'])->shouldBeCalled();

        $builder->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $factory->createMetric('Weight')->shouldBeCalledTimes(3)->willReturn($metric);

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product3, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }
}
