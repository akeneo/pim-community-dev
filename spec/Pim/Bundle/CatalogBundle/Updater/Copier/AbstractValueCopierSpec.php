<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Updater\Copier\AbstractValueCopier;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class AbstractValueCopierSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $productBuilder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beAnInstanceOf('spec\Pim\Bundle\CatalogBundle\Updater\Copier\ConcreteValueCopier');
        $this->beConstructedWith($productBuilder, $attrValidatorHelper);
    }

    function it_is_a_setter()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface');
    }

    function it_throws_an_exception_when_locale_is_expected(
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects a locale, none given.');

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(true);
        $attrValidatorHelper->validateLocale($attribute, null)->willThrow($e);

        $this->shouldThrow(
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'copier', 'concrete')
        )->during('testLocaleAndScope', [$attribute, null, Argument::any()]);
    }

    function it_throws_an_exception_when_locale_is_not_expected(
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $e = new \LogicException('Attribute "attributeCode" does not expect a locale, "en_US" given.');

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(false);
        $attrValidatorHelper->validateLocale($attribute, 'en_US')->willThrow($e);

        $this->shouldThrow(
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'copier', 'concrete')
        )->during('testLocaleAndScope', [$attribute, 'en_US', 'ecommerce']);
    }

    function it_throws_an_exception_when_locale_is_expected_but_not_activated(
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects an existing and activated locale, "uz-UZ" given.');

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(true);
        $attrValidatorHelper->validateLocale($attribute, 'uz-UZ')->willThrow($e);

        $this->shouldThrow(
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'copier', 'concrete')
        )->during('testLocaleAndScope', [$attribute, 'uz-UZ', 'ecommerce']);
    }

    function it_throws_an_exception_when_scope_is_expected(
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects a scope, none given.');

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attrValidatorHelper->validateLocale($attribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, null)->willThrow($e);

        $this->shouldThrow(
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'copier', 'concrete')
        )->during('testLocaleAndScope', [$attribute, null, null]);
    }

    function it_throws_an_exception_when_scope_is_not_expected(
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $e = new \LogicException('Attribute "attributeCode" does not expect a scope, "ecommerce" given.');

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attrValidatorHelper->validateLocale($attribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, 'ecommerce')->willThrow($e);

        $this->shouldThrow(
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'copier', 'concrete')
        )->during('testLocaleAndScope', [$attribute, null, 'ecommerce']);
    }

    function it_throws_an_exception_when_scope_is_expected_but_not_existing(
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects an existing scope, "ecommerce" given.');

        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attrValidatorHelper->validateLocale($attribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, 'ecommerce')->willThrow($e);

        $this->shouldThrow(
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'copier', 'concrete')
        )->during('testLocaleAndScope', [$attribute, null, 'ecommerce']);
    }

    function it_throws_an_exception_when_unit_families_are_not_consistent(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute
    ) {
        $e = new \LogicException('Metric families are not the same for attributes: "fromCode" and "toCode".');

        $fromAttribute->getCode()->willReturn('fromCode');
        $toAttribute->getCode()->willReturn('toCode');
        $attrValidatorHelper->validateUnitFamilies($fromAttribute, $toAttribute)->willThrow($e);

        $this->shouldThrow(
            InvalidArgumentException::expectedFromPreviousException($e, 'fromCode && toCode', 'copier', 'concrete')
        )->during('testUnitFamily', [$fromAttribute, $toAttribute]);
    }
}

class ConcreteValueCopier extends AbstractValueCopier
{
    function copyValue(
        array $products,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        // needs to be implemented
    }

    function testLocaleAndScope(AttributeInterface $attribute, $locale, $scope)
    {
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'concrete');
    }

    function testUnitFamily(AttributeInterface $from, AttributeInterface $to)
    {
        $this->checkUnitFamily($from, $to, 'concrete');
    }
}
