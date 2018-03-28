<?php

namespace spec\Pim\Component\ReferenceData\Updater\Copier;

use Acme\Bundle\AppBundle\Entity\Color;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class ReferenceDataCollectionAttributeCopierSpec extends ObjectBehavior
{
    function let(EntityWithValuesBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['pim_reference_data_multiselect'],
            ['pim_reference_data_multiselect']
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
        $referenceDataColorAttribute->getType()->willReturn('pim_reference_data_multiselect');
        $referenceDataFabricAttribute->getType()->willReturn('pim_reference_data_multiselect');
        $referenceDataColorAttribute->getReferenceDataName()->willReturn('colors');
        $referenceDataFabricAttribute->getReferenceDataName()->willReturn('fabrics');
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
        ValueInterface $fromValue,
        ValueInterface $toValue,
        ArrayCollection $fromCollection,
        Color $black,
        Color $red,
        \ArrayIterator $referenceDataIterator
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');
        $fromAttribute->getReferenceDataName()->willReturn('colors');
        $toAttribute->getReferenceDataName()->willReturn('colors');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromValue->getData()->willReturn($fromCollection);
        $fromCollection->getIterator()->willReturn($referenceDataIterator);
        $referenceDataIterator->rewind()->shouldBeCalled();
        $referenceDataIterator->valid()->willReturn(true, true, false);
        $referenceDataIterator->current()->willReturn($red, $black);
        $referenceDataIterator->next()->shouldBeCalled();

        $red->getCode()->willReturn('red');
        $black->getCode()->willReturn('black');

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromValue);
        $builder
            ->addOrReplaceValue($product1, $toAttribute, $toLocale, $toScope, ['red', 'black'])
            ->shouldBeCalled()
            ->willReturn($toValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder->addOrReplaceValue($product2, Argument::cetera())->shouldNotBeCalled();

        $products = [$product1, $product2];
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
