<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class OptionFilterSpec extends ObjectBehavior
{
    function let(
        QueryBuilder $qb,
        AttributeValidatorHelper $attrValidatorHelper,
        ObjectIdResolverInterface $objectIdResolver
    ) {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $objectIdResolver,
            ['pim_catalog_simpleselect'],
            ['IN', 'EMPTY', 'NOT EMPTY', 'NOT IN']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'EMPTY', 'NOT EMPTY', 'NOT IN']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator(Argument::any())->shouldReturn(false);
    }

    function it_supports_simple_select_attribute(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['1', '2'], null, null, ['field' => 'options_code.id']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute, Expr $expr)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn($expr);

        $expr->isNull(Argument::any())->shouldBeCalled()->willReturn('filteroption_code.option IS NULL');

        $qb->leftJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();
        $qb->andWhere('filteroption_code.option IS NULL')->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null, null, null, ['field' => 'options_code.id']);
    }

    function it_adds_a_not_empty_filter_to_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn($expr);

        $expr->isNotNull(Argument::any())->shouldBeCalled()->willReturn('filteroption_code.option IS NOT NULL');
        $qb->leftJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->andWhere('filteroption_code.option IS NOT NULL')->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT EMPTY', null, null, null, ['field' => 'options_code.id']);
    }

    function it_adds_a_not_in_filter_to_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute,
        Expr $expr,
        Expr\Func $func
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->expr()->willReturn($expr);
        $qb->getRootAlias()->willReturn('r');
        $expr->notIn(Argument::any(), [10, 12])
            ->shouldBeCalled()
            ->willReturn($func);
        $func->__toString()->willReturn('filtercode.option NOT IN (10, 12)');

        $qb->innerJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::containingString('.attribute = 42 AND filtercode.option NOT IN (10, 12)')
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT IN', [10, 12], null, null, ['field' => 'options_code.id']);
    }

    function it_throws_an_exception_if_value_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidPropertyTypeException::arrayExpected('option_code', 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\OptionFilter', 'WRONG'))->during('addAttributeFilter', [$attribute, 'IN', 'WRONG', null, null, ['field' => 'option_code.id']]);
    }

    function it_throws_an_exception_if_the_content_of_value_are_not_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidPropertyTypeException::numericExpected(
            'option_code', 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\OptionFilter',
            'not numeric'
        ))->during('addAttributeFilter', [$attribute, 'IN', [123, 'not numeric'], null, null, ['field' => 'option_code.id']]);
    }
}
