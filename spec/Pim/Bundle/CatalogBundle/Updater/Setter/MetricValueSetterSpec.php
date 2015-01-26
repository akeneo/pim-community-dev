<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MetricValueSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        MetricFactory $factory,
        MeasureManager $measureManager,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $factory,
            $measureManager,
            ['pim_catalog_metric']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_supports_metric_attributes(
        AttributeInterface $metrictAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $metrictAttribute->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supports($metrictAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_a_value(
        $attrValidatorHelper,
        $measureManager,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();
        $attribute->getMetricFamily()->willReturn('Weight');

        $measureManager->getUnitSymbolsForFamily('Weight')
            ->shouldBeCalled()
            ->willReturn(['KILOGRAM' => 'kg', 'GRAM' => 'g']);

        $data = ['data' => 107, 'unit' => 'KILOGRAM'];
        $this->setValue([], $attribute, $data, 'fr_FR', 'mobile');
    }

    function it_throws_an_error_if_data_is_not_array(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'Not an array';

        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('attributeCode', 'setter', 'metric', gettype($data))
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_there_is_no_data_key_in_array(
        AttributeInterface $attribute
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
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_there_is_no_unit_key_in_array(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['data' => 'data value'];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('attributeCode', 'unit', 'setter', 'metric',
                print_r($data, true))
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_data_is_not_a_number_or_null(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['data' => 'text', 'unit' => 'KILOGRAM'];

        $this->shouldThrow(
            InvalidArgumentException::arrayNumericKeyExpected(
                'attributeCode',
                'data',
                'setter',
                'metric',
                'string'
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_unit_is_not_a_string(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['data' => 42, 'unit' => 123];

        $this->shouldThrow(
            InvalidArgumentException::arrayStringKeyExpected(
                'attributeCode',
                'unit',
                'setter',
                'metric',
                123
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_unit_does_not_exist(
        AttributeInterface $attribute,
        $measureManager
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->getMetricFamily()->willReturn('Weight');

        $data = ['data' => 42, 'unit' => 'incorrect unit'];

        $measureManager->getUnitSymbolsForFamily('Weight')
            ->shouldBeCalled()
            ->willReturn(['KILOGRAM' => 'kg', 'GRAM' => 'g']);

        $this->shouldThrow(
            InvalidArgumentException::arrayInvalidKey(
                'attributeCode',
                'unit',
                'The unit does not exist',
                'setter',
                'metric',
                'incorrect unit'
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_numeric_value_to_a_product_value(
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        $builder,
        $measureManager,
        $factory,
        MetricInterface $metric,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = ['data' => 107, 'unit' => 'KILOGRAM'];

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->getMetricFamily()->willReturn('Weight');

        $measureManager->getUnitSymbolsForFamily('Weight')
            ->shouldBeCalled()
            ->willReturn(['KILOGRAM' => 'kg', 'GRAM' => 'g']);

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

        $products = [$product1, $product2, $product3];

        $this->setValue($products, $attribute, $data, $locale, $scope);
    }
}
