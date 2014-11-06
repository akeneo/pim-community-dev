<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;

class MetricValueSetterSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder, MetricFactory $factory, MeasureManager $measureManager)
    {
        $this->beConstructedWith($builder, $factory, $measureManager);
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

    function it_throws_an_error_if_data_is_not_array(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'Not an array';

        $this->shouldThrow(
            new \InvalidArgumentException('$data have to be an array')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_there_is_no_data_key_in_array(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['unit' => 'kg'];

        $this->shouldThrow(
            new \LogicException('Missing "data" key in array')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_there_is_no_unit_key_in_array(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['data' => 'data value'];

        $this->shouldThrow(
            new \LogicException('Missing "unit" key in array')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_data_is_not_a_number(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['data' => 'text', 'unit' => 'kg'];

        $this->shouldThrow(
            new \LogicException('Invalid data type or invalid unit type')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_unit_is_not_a_string(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['data' => 42, 'unit' => 123];

        $this->shouldThrow(new \LogicException('Invalid data type or invalid unit type'))->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_unit_does_not_exist(
        AttributeInterface $attribute,
        $measureManager
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->getMetricFamily()->willReturn('Weight');

        $data = ['data' => 42, 'unit' => 'incorrect unit'];

        $measureManager->unitExistsInFamily($data['unit'], 'Weight')->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(new \LogicException('"incorrect unit" does not exist in any attribute\'s families'))->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_numeric_value_to_a_product_value(
        AttributeInterface $attribute,
        AbstractProduct $product1,
        AbstractProduct $product2,
        AbstractProduct $product3,
        $builder,
        $measureManager,
        $factory,
        MetricInterface $metric,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = ['data' => 107, 'unit' => 'kg'];

        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->getMetricFamily()->willReturn('Weight');

        $measureManager->unitExistsInFamily($data['unit'], 'Weight')->shouldBeCalled()->willReturn(true);

        $productValue->getMetric()->willReturn(null);
        $productValue->setMetric($metric)->shouldBeCalled();

        $metric->setUnit($data['unit'])->shouldBeCalled();
        $metric->setData($data['data'])->shouldBeCalled();

        $builder
            ->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $factory->createMetric('Weight')->shouldBeCalled()->willReturn($metric);

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->willReturn($productValue);

        $products = [$product1, $product2, $product3];

        $this->setValue($products, $attribute, $data, $locale, $scope);
    }
}
