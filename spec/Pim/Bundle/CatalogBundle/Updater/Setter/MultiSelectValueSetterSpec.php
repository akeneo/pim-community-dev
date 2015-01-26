<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MultiSelectValueSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeOptionRepositoryInterface $attrOptionRepository,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $attrOptionRepository,
            ['pim_catalog_multiselect']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_supports_multiselect_attributes(
        AttributeInterface $multiSelectAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $multiSelectAttribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supports($multiSelectAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_a_value(
        $attrValidatorHelper,
        $attrOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $attributeOption
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();
        $attributeOption->getCode()->willReturn('attributeOptionCode');

        $attrOptionRepository
            ->findOneBy(['code' => 'attributeOptionCode', 'attribute' => $attribute])
            ->shouldBeCalledTimes(1)
            ->willReturn($attributeOption);

        $this->setValue([], $attribute, ['attributeOptionCode'], 'fr_FR', 'mobile');
    }

    function it_throws_an_error_if_data_is_not_an_array_of_option_codes(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['foo' => ['bar' => 'baz']];

        $this->shouldThrow(
            InvalidArgumentException::arrayStringKeyExpected(
                'attributeCode',
                'foo',
                'setter',
                'multi select',
                'array'
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_an_option_code_is_unknown(
        $attrOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['unknown code'];

        $attrOptionRepository
            ->findOneBy(['code' => 'unknown code', 'attribute' => $attribute])
            ->shouldBeCalledTimes(1)
            ->willReturn(null);

        $this->shouldThrow(
            InvalidArgumentException::arrayInvalidKey(
                'attributeCode',
                'code',
                'The option does not exist',
                'setter',
                'multi select',
                'unknown code'
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_multiselect_value_to_a_product_value(
        $builder,
        $attrOptionRepository,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductValueInterface $productValue,
        AttributeOptionInterface $attributeOption,
        AttributeOptionInterface $oldOption
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attribute->getCode()->willReturn('attributeCode');

        $attributeOption->getCode()->willReturn('attributeOptionCode');

        $attrOptionRepository
            ->findOneBy(['code' => 'attributeOptionCode', 'attribute' => $attribute])
            ->shouldBeCalledTimes(1)
            ->willReturn($attributeOption);

        $productValue->getOptions()->willReturn([$oldOption]);
        $productValue->removeOption($oldOption)->shouldBeCalled();
        $productValue->addOption($attributeOption)->shouldBeCalled();

        $builder
            ->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);

        $this->setValue([$product1, $product2, $product3], $attribute, ['attributeOptionCode'], $locale, $scope);
    }
}
