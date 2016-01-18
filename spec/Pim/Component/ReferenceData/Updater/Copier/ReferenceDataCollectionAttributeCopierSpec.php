<?php

namespace spec\Pim\Component\ReferenceData\Updater\Copier;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
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
        ProductInterface $product4,
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
        $toProductValue->removeColor(Argument::any())->shouldBeCalledTimes(3);
        $toProductValue->addColor(Argument::any())->shouldBeCalledTimes(6);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $product4->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product4->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $builder->addProductValue($product3, $toAttribute, $toLocale, $toScope)->shouldBeCalledTimes(1)->willReturn($toProductValue);

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

    function it_throws_error_when_getter_method_of_the_reference_data_is_not_implemented(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        CustomProductValueBis $fromProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';
        $toProductValue = new InvalidGetterCustomProductValueBis();

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

        $this->shouldThrow(new \LogicException('ProductValue method "getColors" is not implemented'))->during('copyAttributeData', [
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

    function it_throws_error_when_remover_method_of_the_reference_data_is_not_implemented(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        CustomProductValueBis $fromProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';
        $toProductValue = new InvalidRemoverCustomProductValue();

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

        $this->shouldThrow(new \LogicException('ProductValue method "removeColor" is not implemented'))->during('copyAttributeData', [
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

    function it_throws_error_when_adder_method_of_the_reference_data_is_not_implemented(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        CustomProductValueBis $fromProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';
        $toProductValue = new InvalidAdderCustomProductValue();

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

        $this->shouldThrow(new \LogicException('ProductValue method "addColor" is not implemented'))->during('copyAttributeData', [
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

class InvalidRemoverCustomProductValue extends AbstractProductValue
{
    protected $colors;

    public function getColors()
    {
    }
}

class InvalidAdderCustomProductValue extends AbstractProductValue
{
    protected $colors;

    public function __construct()
    {
        $this->colors = new ArrayCollection();
    }

    public function getColors()
    {
        return $this->colors;
    }

    public function removeColor(ReferenceDataInterface $referenceData)
    {
    }
}
