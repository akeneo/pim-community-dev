<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
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
        $factory,
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
        $product->removeValue($metricValue)->shouldBeCalled()->willReturn($product);
        $factory->createMetric('Weight', 'KILOGRAM', 107)->willReturn($metric);

        $data = ['amount' => 107, 'unit' => 'KILOGRAM'];
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

    function it_throws_an_error_if_there_is_no_attribute_amount_key_in_array(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['unit' => 'KILOGRAM'];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected(
                'attributeCode',
                'amount',
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

        $data = ['amount' => 'data value'];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('attributeCode', 'unit', 'setter', 'metric',
                print_r($data, true))
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_sets_numeric_attribute_data_to_a_product_value(
        $builder,
        $factory,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        MetricInterface $metric,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = ['amount' => 42, 'unit' => 'KILOGRAM'];

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->getMetricFamily()->willReturn('Weight');

        $factory->createMetric('Weight', $data['unit'], $data['amount'])->shouldBeCalledTimes(2)->willReturn($metric);

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $product1->removeValue($productValue)->shouldBeCalled()->willReturn($product1);
        $builder
            ->addProductValue($product1, $attribute, $locale, $scope, $metric)
            ->willReturn($productValue);

        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product2->removeValue(null)->shouldNotBeCalled();
        $builder
            ->addProductValue($product2, $attribute, $locale, $scope, $metric)
            ->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }

    function it_sets_non_numeric_attribute_data_to_a_product_value(
        $builder,
        $factory,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        MetricInterface $metric,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = ['amount' => 'foobar', 'unit' => 'KILOGRAM'];

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->getMetricFamily()->willReturn('Weight');

        $factory->createMetric('Weight', $data['unit'], $data['amount'])->shouldBeCalledTimes(2)->willReturn($metric);

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $product1->removeValue($productValue)->shouldBeCalled()->willReturn($product1);
        $builder
            ->addProductValue($product1, $attribute, $locale, $scope, $metric)
            ->willReturn($productValue);

        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product2->removeValue(null)->shouldNotBeCalled();
        $builder
            ->addProductValue($product2, $attribute, $locale, $scope, $metric)
            ->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }
}
