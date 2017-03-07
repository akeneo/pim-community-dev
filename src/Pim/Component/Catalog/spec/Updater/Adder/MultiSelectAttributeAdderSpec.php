<?php

namespace spec\Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class MultiSelectAttributeAdderSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder)
    {
        $this->beConstructedWith($builder, ['pim_catalog_multiselect']);
    }

    function it_is_a_adder()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Adder\AdderInterface');
    }

    function it_supports_multi_select_attributes(
        AttributeInterface $multiSelectAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $multiSelectAttribute->getType()->willReturn('pim_catalog_multiselect');
        $this->supports($multiSelectAttribute)->shouldReturn(true);

        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_throws_an_error_if_attribute_data_is_not_an_array(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not an array';

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Adder\MultiSelectAttributeAdder',
                $data
            )
        )->during('addAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_adds_attribute_data_on_multiselect_value_to_a_product_value(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValueInterface $productValue,
        ArrayCollection $optionsValue,
        AttributeOptionInterface $option,
        \ArrayIterator $valuesIterator
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attribute->getCode()->willReturn('attributeCode');

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);

        $productValue->getOptions()->shouldBeCalledTimes(1)->willReturn($optionsValue);
        $optionsValue->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, false);
        $valuesIterator->current()->willReturn($option);
        $valuesIterator->next()->shouldBeCalled();

        $option->getCode()->willReturn('previousOptionCode');

        $builder
            ->addOrReplaceProductValue($product1, $attribute, $locale, $scope, ['optionCode', 'previousOptionCode'])
            ->shouldBeCalled();

        $builder
            ->addOrReplaceProductValue($product2, $attribute, $locale, $scope, ['optionCode'])
            ->shouldBeCalled();

        $this->addAttributeData($product1, $attribute, ['optionCode'], ['locale' => $locale, 'scope' => $scope]);
        $this->addAttributeData($product2, $attribute, ['optionCode'], ['locale' => $locale, 'scope' => $scope]);
    }
}
