<?php

namespace spec\Pim\Component\Catalog\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MultiSelectAttributeCopierSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        AttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['pim_catalog_multiselect'],
            ['pim_catalog_multiselect'],
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
        AttributeInterface $fromMultiSelectAttribute,
        AttributeInterface $toMultiSelectAttribute
    ) {
        $fromMultiSelectAttribute->getType()->willReturn('pim_catalog_multiselect');
        $toMultiSelectAttribute->getType()->willReturn('pim_catalog_multiselect');
        $this->supportsAttributes($fromMultiSelectAttribute, $toMultiSelectAttribute)->shouldReturn(true);

        $fromTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $toTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromIdentifierAttribute->getType()->willReturn('pim_catalog_identifier');
        $toTextareaAttribute->getType()->willReturn('pim_catalog_text');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromMultiSelectAttribute->getType()->willReturn('pim_catalog_number');
        $toTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $this->supportsAttributes($fromTextAttribute, $toMultiSelectAttribute)->shouldReturn(false);
        $this->supportsAttributes($fromMultiSelectAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_multi_select_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        ProductValue $fromProductValue,
        ProductValue $toProductValue,
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

        $fromProductValue->getOptions()->willReturn([$fromAttributeOption])->shouldBeCalled(3);

        $toProductValue->getOptions()->willReturn([$toAttributeOption]);
        $toProductValue->removeOption($toAttributeOption)->shouldBeCalled();

        $fromAttributeOption->getCode()->willReturn('attributeOption');
        $attributeOptionRepository
            ->findOneByIdentifier('toAttributeCode.attributeOption')
            ->willReturn($toAttributeOption);
        $toProductValue->addOption($toAttributeOption)->shouldBeCalledTimes(3);

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
