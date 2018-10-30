<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\MultiSelectAttributeAdder;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;

class MultiSelectAttributeAdderSpec extends ObjectBehavior
{
    function let(EntityWithValuesBuilderInterface $builder)
    {
        $this->beConstructedWith($builder, ['pim_catalog_multiselect']);
    }

    function it_is_a_adder()
    {
        $this->shouldImplement(AdderInterface::class);
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
                MultiSelectAttributeAdder::class,
                $data
            )
        )->during('addAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_adds_attribute_data_on_multiselect_value_to_a_product_value(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        OptionsValueInterface $value
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attribute->getCode()->willReturn('attributeCode');

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($value);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);

        $value->getOptionCodes()->willReturn(['optionCode', 'previousOptionCode']);

        $builder
            ->addOrReplaceValue($product1, $attribute, $locale, $scope, ['optionCode', 'previousOptionCode'])
            ->shouldBeCalled();

        $builder
            ->addOrReplaceValue($product2, $attribute, $locale, $scope, ['optionCode'])
            ->shouldBeCalled();

        $this->addAttributeData($product1, $attribute, ['optionCode'], ['locale' => $locale, 'scope' => $scope]);
        $this->addAttributeData($product2, $attribute, ['optionCode'], ['locale' => $locale, 'scope' => $scope]);
    }
}
