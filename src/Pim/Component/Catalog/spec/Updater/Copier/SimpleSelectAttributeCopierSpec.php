<?php

namespace spec\Pim\Component\Catalog\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class SimpleSelectAttributeCopierSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        AttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['pim_catalog_simpleselect'],
            ['pim_catalog_simpleselect'],
            $attributeOptionRepository
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
        AttributeInterface $fromSimpleSelectAttribute,
        AttributeInterface $toSimpleSelectAttribute
    ) {
        $fromSimpleSelectAttribute->getType()->willReturn('pim_catalog_simpleselect');
        $toSimpleSelectAttribute->getType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttributes($fromSimpleSelectAttribute, $toSimpleSelectAttribute)->shouldReturn(true);

        $fromTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $toTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromIdentifierAttribute->getType()->willReturn('pim_catalog_identifier');
        $toTextareaAttribute->getType()->willReturn('pim_catalog_text');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromSimpleSelectAttribute->getType()->willReturn('pim_catalog_number');
        $toTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $this->supportsAttributes($fromTextAttribute, $toSimpleSelectAttribute)->shouldReturn(false);
        $this->supportsAttributes($fromSimpleSelectAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_simple_select_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        ProductValueInterface $fromProductValue,
        ProductValueInterface $toProductValue,
        AttributeOptionInterface $fromAttributeOption,
        AttributeOptionInterface $toAttributeOption
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getData()->willReturn($fromAttributeOption);
        $fromAttributeOption->getCode()->willReturn('attributeOption');
        $attributeOptionRepository
            ->findOneByIdentifier('toAttributeCode.attributeOption')
            ->willReturn($toAttributeOption);
        $toProductValue->setOption($toAttributeOption)->shouldBeCalledTimes(3);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $product4->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product4->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $builder
            ->addOrReplaceProductValue($product3, $toAttribute, $toLocale, $toScope)
            ->shouldBeCalledTimes(1)
            ->willReturn($toProductValue);

        $products = [$product1, $product2, $product3, $product4];
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
