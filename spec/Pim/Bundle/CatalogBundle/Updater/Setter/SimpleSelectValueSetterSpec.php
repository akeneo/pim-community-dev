<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class SimpleSelectValueSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeOptionRepository $attrOptionRepository,
        AttributeValidatorHelper $attributeValidatorHelper
    ) {
        $this->beConstructedWith(
            $builder,
            $attributeValidatorHelper,
            $attrOptionRepository,
            ['pim_catalog_simpleselect']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_supports_simpleselect_attributes(
        AttributeInterface $simpleSelectAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $simpleSelectAttribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $this->supports($simpleSelectAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_a_value(
        $attributeValidatorHelper,
        $attrOptionRepository,
        AttributeInterface $attribute,
        AttributeOption $attributeOption
    ) {
        $attributeValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attributeValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attributeOption->getCode()->willReturn('attributeOptionCode');
        $attrOptionRepository
            ->findOneBy(['code' => 'attributeOptionCode', 'attribute' => $attribute])
            ->shouldBeCalledTimes(1)
            ->willReturn($attributeOption);

        $data = ['attribute' => $attribute, 'code' => 'attributeOptionCode', 'label' => []];
        $this->setValue([], $attribute, $data, 'fr_FR', 'mobile');
    }

    function it_throws_an_error_if_data_is_not_an_array(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not a simple select option';

        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('attributeCode', 'setter', 'simple select', gettype($data))
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_there_is_no_attribute_key(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['no attribute key' => 'value'];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected(
                'attributeCode',
                'attribute',
                'setter',
                'simple select',
                gettype($data)
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_there_is_no_code_key(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['attribute' => 'value', 'no code' => 'value'];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected(
                'attributeCode',
                'code',
                'setter',
                'simple select',
                gettype($data)
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_there_the_option_is_unknown(
        $attrOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $attrOptionRepository
            ->findOneBy(['code' => 'unknown code', 'attribute' => $attribute])
            ->willReturn(null);

        $data = ['attribute' => 'value', 'code' => 'unknown code'];

        $this->shouldThrow(
            InvalidArgumentException::arrayInvalidKey(
                'attributeCode',
                'code',
                'Option with code "unknown code" does not exist',
                'setter',
                'simple select',
                gettype($data)
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_simpleselect_value_to_a_product_value(
        $builder,
        $attrOptionRepository,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductValue $productValue,
        AttributeOption $attributeOption
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attribute->getCode()->willReturn('attributeCode');

        $attributeOption->getCode()->willReturn('attributeOptionCode');

        $attrOptionRepository
            ->findOneBy(['code' => 'attributeOptionCode', 'attribute' => $attribute])
            ->shouldBeCalledTimes(1)
            ->willReturn($attributeOption);

        $data = ['attribute' => $attribute, 'code' => 'attributeOptionCode', 'label' => []];
        $productValue->setOption(Argument::any())->shouldBeCalled();

        $builder
            ->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->willReturn($productValue);

        $products = [$product1, $product2, $product3];

        $this->setValue($products, $attribute, $data, $locale, $scope);
    }
}
