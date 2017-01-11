<?php

namespace spec\Pim\Component\ReferenceData\Updater\Copier;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Prophecy\Argument;

class ReferenceDataCollectionAttributeCopierSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
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
        $referenceDataColorAttribute->getAttributeType()->willReturn('pim_reference_data_multiselect');
        $referenceDataFabricAttribute->getAttributeType()->willReturn('pim_reference_data_multiselect');
        $referenceDataColorAttribute->getReferenceDataName()->willReturn('colors');
        $referenceDataFabricAttribute->getReferenceDataName()->willReturn('fabrics');
        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
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
        ProductInterface $product3,
        CustomProductValueBis $fromProductValue,
        CustomProductValueBis $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromCollection = new ArrayCollection([new Color(), new Color()]);
        $toCollection = new ArrayCollection([new Color()]);

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');
        $fromAttribute->getReferenceDataName()->willReturn('colors');
        $toAttribute->getReferenceDataName()->willReturn('colors');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getColors()->willReturn($fromCollection);
        $toProductValue->getColors()->willReturn($toCollection);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->shouldBeCalled()->willReturn($toProductValue);
        $product1->removeValue($toProductValue)->shouldBeCalled()->willReturn($product1);
        $builder
            ->addProductValue($product1, $toAttribute, $toLocale, $toScope, $fromCollection)
            ->shouldBeCalled()
            ->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->shouldNotBeCalled();
        $product2->removeValue(Argument::any())->shouldNotBeCalled();
        $builder
            ->addProductValue($product2, $toAttribute, $toLocale, $toScope, $fromCollection)
            ->shouldNotBeCalled();

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->shouldBeCalled()->willReturn(null);
        $product3->removeValue(null)->shouldNotBeCalled();
        $builder
            ->addProductValue($product3, $toAttribute, $toLocale, $toScope, $fromCollection)
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

    function it_throws_error_when_getter_method_of_the_reference_data_is_not_implemented(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        InvalidGetterCustomProductValueBis $fromProductValue,
        InvalidGetterCustomProductValueBis $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $fromScope = 'mobile';
        $toScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');
        $fromAttribute->getReferenceDataName()->willReturn('colors');
        $toAttribute->getReferenceDataName()->willReturn('colors');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProduct->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $fromProduct->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $toProduct->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $toProduct->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $toProduct->removeValue($toProductValue)->shouldBeCalled()->willReturn($toProduct);

        $this
            ->shouldThrow(new \LogicException('ProductValue method "getColors" is not implemented'))
            ->during('copyAttributeData', [
                $fromProduct,
                $toProduct,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            ]);
    }
}

class Color implements ReferenceDataInterface
{
    public function getId()
    {
    }
    public function getCode()
    {
    }
    public function setCode($code)
    {
    }
    public function getSortOrder()
    {
    }
    public static function getLabelProperty()
    {
    }
    public function __toString()
    {
    }
}

class CustomProductValueBis extends AbstractProductValue
{
    public function setColors(Collection $referenceData = null)
    {
    }
    public function getColors()
    {
    }
    public function removeColor(ReferenceDataInterface $referenceData)
    {
    }
    public function addColor(ReferenceDataInterface $referenceData)
    {
    }
}

class InvalidGetterCustomProductValueBis extends AbstractProductValue
{
    protected $color;
}
