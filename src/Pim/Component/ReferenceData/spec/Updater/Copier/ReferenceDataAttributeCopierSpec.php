<?php

namespace spec\Pim\Component\ReferenceData\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Prophecy\Argument;

class ReferenceDataAttributeCopierSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['pim_reference_data_simpleselect'],
            ['pim_reference_data_simpleselect']
        );
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Copier\CopierInterface');
    }

    function it_supports_same_reference_data_attributes(
        AttributeInterface $textareaAttribute,
        AttributeInterface $referenceDataColorAttribute,
        AttributeInterface $referenceDataFabricAttribute
    ) {
        $referenceDataColorAttribute->getType()->willReturn('pim_reference_data_simpleselect');
        $referenceDataFabricAttribute->getType()->willReturn('pim_reference_data_simpleselect');
        $referenceDataColorAttribute->getReferenceDataName()->willReturn('color');
        $referenceDataFabricAttribute->getReferenceDataName()->willReturn('fabric');
        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $textareaAttribute->getReferenceDataName()->willReturn(null);

        $this->supportsAttributes($referenceDataColorAttribute, $referenceDataColorAttribute)->shouldReturn(true);
        $this->supportsAttributes($referenceDataColorAttribute, $referenceDataFabricAttribute)->shouldReturn(false);
        $this->supportsAttributes($textareaAttribute, $referenceDataFabricAttribute)->shouldReturn(false);
        $this->supportsAttributes($referenceDataColorAttribute, $textareaAttribute)->shouldReturn(false);
    }

    function it_copies_reference_data_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        CustomProductValue $fromProductValue,
        CustomProductValue $toProductValue,
        ReferenceDataInterface $referenceData
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');
        $fromAttribute->getReferenceDataName()->willReturn('color');
        $toAttribute->getReferenceDataName()->willReturn('color');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getColor()->willReturn($referenceData);
        $referenceData->getCode()->willReturn('black');

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $builder
            ->addOrReplaceProductValue($product1, $toAttribute, $toLocale, $toScope, 'black')
            ->shouldBeCalled()
            ->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder->addOrReplaceProductValue($product2, Argument::cetera())->shouldNotBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale'   => $toLocale,
                    'from_scope'  => $fromScope,
                    'to_scope'    => $toScope
                ]
            );
        }
    }

    function it_throws_error_when_getter_method_of_the_reference_data_is_not_implemented(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        InvalidGetterCustomProductValue $fromProductValue,
        CustomProductValue $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');
        $fromAttribute->getReferenceDataName()->willReturn('color');
        $toAttribute->getReferenceDataName()->willReturn('color');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProduct->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $fromProduct->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $toProduct->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $toProduct->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $this->shouldThrow(new \LogicException('ProductValue method "getColor" is not implemented'))->during(
            'copyAttributeData',
            [
                $fromProduct,
                $toProduct,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale'   => $toLocale,
                    'from_scope'  => $fromScope,
                    'to_scope'    => $toScope
                ]
            ]
        );
    }
}

class CustomProductValue extends AbstractProductValue
{
    public function setColor(ReferenceDataInterface $referenceData = null)
    {
    }
    public function getColor()
    {
    }
}

class InvalidGetterCustomProductValue extends AbstractProductValue
{
    public function setColor(ReferenceDataInterface $referenceData = null)
    {
    }
}
