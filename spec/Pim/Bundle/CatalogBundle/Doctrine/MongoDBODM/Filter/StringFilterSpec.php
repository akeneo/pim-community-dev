<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class StringFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_identifier'],
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN', 'EMPTY', 'NOT EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\StringFilter');
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'STARTS WITH',
            'ENDS WITH',
            'CONTAINS',
            'DOES NOT CONTAIN',
            '=',
            'IN',
            'EMPTY',
            'NOT EMPTY'
        ]);
        $this->supportsOperator('ENDS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_starts_with_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/^My Sku/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'STARTS WITH', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_ends_with_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku$/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'ENDS WITH', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_contains_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'CONTAINS', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_does_not_contain_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/^((?!My Sku).)*$/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'DOES NOT CONTAIN', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_an_empty_attribute_filter_in_the_query($attrValidatorHelper, $qb, AttributeInterface $sku)
    {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->exists(false)->shouldBeCalled()->willReturn($qb);

        $this->addAttributeFilter($sku, 'EMPTY', null, null, null, ['field' => 'sku']);
    }

    function it_adds_a_not_empty_attribute_filter_in_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $sku,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($sku, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($sku, Argument::any())->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->expr()->willReturn($expr);
        $expr->field('normalizedData.sku')->shouldBeCalled()->willReturn($expr);
        $expr->exists(true)->shouldBeCalled()->willReturn($expr);
        $expr->notEqual('')->shouldBeCalled()->willReturn($expr);
        $qb->addAnd(
            Argument::type('Doctrine\ODM\MongoDB\Query\Expr'),
            Argument::type('Doctrine\ODM\MongoDB\Query\Expr')
        )->shouldBeCalled();

        $this->addAttributeFilter($sku, 'NOT EMPTY', null, null, null, ['field' => 'sku']);
    }


    function it_throws_an_exception_if_value_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attributeCode');

        $this->shouldThrow(InvalidArgumentException::stringExpected('attributeCode', 'filter', 'string', gettype(123)))
            ->during('addAttributeFilter', [$attribute, '=', 123, null, null, ['field' => 'attributeCode']]);
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'filter', 'string')
        )->during('addAttributeFilter', [$attribute, '=', 123, null, null, ['field' => 'attributeCode']]);
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'filter', 'string')
        )->during('addAttributeFilter', [$attribute, '=', 123, 'en_US', 'ecommerce', ['field' => 'attributeCode']]);
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'filter', 'string')
        )->during('addAttributeFilter', [$attribute, '=', 123, 'uz-UZ', 'ecommerce', ['field' => 'attributeCode']]);
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'filter', 'string')
        )->during('addAttributeFilter', [$attribute, '=', 123, null, null, ['field' => 'attributeCode']]);
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'filter', 'string')
        )->during('addAttributeFilter', [$attribute, '=', 123, null, 'ecommerce', ['field' => 'attributeCode']]);
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
            InvalidArgumentException::expectedFromPreviousException($e, 'attributeCode', 'filter', 'string')
        )->during('addAttributeFilter', [$attribute, '=', 123, null, 'ecommerce', ['field' => 'attributeCode']]);
    }
}
