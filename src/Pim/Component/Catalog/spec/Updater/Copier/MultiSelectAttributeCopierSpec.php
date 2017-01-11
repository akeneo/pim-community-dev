<?php

namespace spec\Pim\Component\Catalog\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MultiSelectAttributeCopierSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['pim_catalog_multiselect'],
            ['pim_catalog_multiselect']
        );
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Copier\CopierInterface');
    }

    function it_supports_multi_select_attributes(
        AttributeInterface $fromTextAttribute,
        AttributeInterface $fromTextareaAttribute,
        AttributeInterface $fromIdentifierAttribute,
        AttributeInterface $toTextareaAttribute,
        AttributeInterface $fromMultiSelectAttribute,
        AttributeInterface $toMultiSelectAttribute
    ) {
        $fromMultiSelectAttribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $toMultiSelectAttribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supportsAttributes($fromMultiSelectAttribute, $toMultiSelectAttribute)->shouldReturn(true);

        $fromTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromIdentifierAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromMultiSelectAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $this->supportsAttributes($fromTextAttribute, $toMultiSelectAttribute)->shouldReturn(false);
        $this->supportsAttributes($fromMultiSelectAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_multi_select_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductValue $fromProductValue,
        ProductValue $toProductValue,
        AttributeOptionInterface $attributeOption
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getOptions()->willReturn([$attributeOption])->shouldBeCalledTimes(2);
        $toProductValue->getOptions()->willReturn([$attributeOption]);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->shouldBeCalled()->willReturn($toProductValue);
        $product1->removeValue($toProductValue)->shouldBeCalled()->willReturn($toProductValue);
        $builder
            ->addProductValue($product1, $toAttribute, $toLocale, $toScope, [$attributeOption])
            ->shouldBeCalled()
            ->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->shouldNotBeCalled();
        $product2->removeValue(null)->shouldNotBeCalled();
        $builder
            ->addProductValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldNotBeCalled();

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->shouldBeCalled()->willReturn(null);
        $product3->removeValue(null)->shouldNotBeCalled();
        $builder
            ->addProductValue($product3, $toAttribute, $toLocale, $toScope, [$attributeOption])
            ->shouldBeCalled()
            ->willReturn($toProductValue);

        $products = [$product1, $product2, $product3];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }
}
