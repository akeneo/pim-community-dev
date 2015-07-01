<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\AbstractAttributeSetter;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

class AbstractAttributeSetterSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $productBuilder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beAnInstanceOf('spec\Pim\Component\Catalog\Updater\Setter\ConcreteAttributeSetter');
        $this->beConstructedWith($productBuilder, $attrValidatorHelper);
    }

    function it_is_a_setter()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\Setter\SetterInterface');
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'setter', 'concrete')
        )->during('testLocaleAndScope', [$attribute, null, 'ecommerce']);
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'setter', 'concrete')
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'setter', 'concrete')
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'setter', 'concrete')
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'setter', 'concrete')
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'setter', 'concrete')
        )->during('testLocaleAndScope', [$attribute, null, 'ecommerce']);
    }
}

class ConcreteAttributeSetter extends AbstractAttributeSetter
{
    function setAttributeData(ProductInterface $product, AttributeInterface $attribute, $data, array $options = [])
    {
        // needs to be implemented
    }

    function testLocaleAndScope(AttributeInterface $attribute, $locale, $scope)
    {
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'concrete');
    }
}
