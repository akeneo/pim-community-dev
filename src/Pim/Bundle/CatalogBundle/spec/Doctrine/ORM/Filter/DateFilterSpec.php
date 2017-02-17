<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class DateFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_date'],
            ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
    }

    function it_is_a_date_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter');
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_date_attributes(AttributeInterface $dateAtt, AttributeInterface $otherAtt)
    {
        $dateAtt->getType()->willReturn('pim_catalog_date');
        $this->supportsAttribute($dateAtt)->shouldReturn(true);

        $otherAtt->getType()->willReturn('pim_catalog_other');
        $this->supportsAttribute($otherAtt)->shouldReturn(false);
    }

    function it_adds_an_equal_filter_on_an_attribute_in_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $attribute,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->getRootAlias()->willReturn('p');

        $qb->innerJoin(
            'p.values',
            Argument::containingString('filterrelease_date'),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->eq(Argument::containingString('.date'), '2014-03-15')->willReturn($comp);
        $comp->__toString()->willReturn('filterrelease_date.date = \'2014-03-15\'');

        $qb->andWhere('filterrelease_date.date = \'2014-03-15\'')->shouldBeCalled();

        $this->addAttributeFilter($attribute, '=', '2014-03-15');
    }

    function it_adds_a_not_equal_filter_on_an_attribute_in_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $attribute,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->getRootAlias()->willReturn('p');

        $qb->innerJoin(
            'p.values',
            Argument::containingString('filterrelease_date'),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->neq(Argument::containingString('.date'), '2014-03-15')->willReturn($comp);
        $comp->__toString()->willReturn('filterrelease_date.date != \'2014-03-15\'');

        $qb->andWhere('filterrelease_date.date != \'2014-03-15\'')->shouldBeCalled();

        $this->addAttributeFilter($attribute, '!=', '2014-03-15');
    }

    function it_adds_a_less_than_filter_on_an_attribute_in_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $attribute,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->getRootAlias()->willReturn('p');

        $qb->innerJoin(
            'p.values',
            Argument::containingString('filterrelease_date'),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->lt(Argument::containingString('.date'), '2014-03-15')->willReturn($comp);
        $comp->__toString()->willReturn('filterrelease_date.date < \'2014-03-15\'');

        $qb->andWhere('filterrelease_date.date < \'2014-03-15\'')->shouldBeCalled();

        $this->addAttributeFilter($attribute, '<', '2014-03-15');
    }

    function it_adds_a_greater_than_filter_on_an_attribute_to_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $attribute,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->getRootAlias()->willReturn('p');

        $qb->innerJoin(
            'p.values',
            Argument::containingString('filterrelease_date'),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->gt(Argument::containingString('.date'), '2014-03-15')->willReturn($comp);
        $comp->__toString()->willReturn('filterrelease_date.date > \'2014-03-15\'');

        $qb->andWhere('filterrelease_date.date > \'2014-03-15\'')->shouldBeCalled();

        $this->addAttributeFilter($attribute, '>', '2014-03-15');
    }

    function it_adds_an_empty_filter_on_an_attribute_to_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->getRootAlias()->willReturn('p');

        $qb->leftJoin(
            'p.values',
            Argument::containingString('filterrelease_date'),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->isNull(Argument::containingString('.date'))->willReturn('filterrelease_date.date IS NULL');

        $qb->andWhere('filterrelease_date.date IS NULL')->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_attribute_in_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->getRootAlias()->willReturn('p');

        $qb->leftJoin(
            'p.values',
            Argument::containingString('filterrelease_date'),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->isNotNull(Argument::containingString('.date'))->willReturn('filterrelease_date.date IS NOT NULL');

        $qb->andWhere('filterrelease_date.date IS NOT NULL')->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT EMPTY', null);
    }

    function it_adds_a_between_filter_on_an_attribute_in_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->getRootAlias()->willReturn('p');

        $qb->innerJoin(
            'p.values',
            Argument::containingString('filterrelease_date'),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->literal('2014-03-18')->willReturn('2014-03-18');

        $qb->andWhere(Argument::containingString('.date BETWEEN 2014-03-15 AND 2014-03-18'))->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'BETWEEN', ['2014-03-15', '2014-03-18']);
    }

    function it_adds_a_not_between_filter_on_an_attribute_in_the_query(
        $attrValidatorHelper,
        $qb,
        AttributeInterface $attribute,
        Expr $expr,
        Expr\Comparison $ltComp,
        Expr\Comparison $gtComp,
        Expr\Orx $or
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->getRootAlias()->willReturn('p');

        $qb->innerJoin(
            'p.values',
            Argument::containingString('filterrelease_date'),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->literal('2014-03-18')->willReturn('2014-03-18');
        $expr->lt(Argument::containingString('.date'), '2014-03-15')->willReturn($ltComp);
        $expr->gt(Argument::containingString('.date'), '2014-03-18')->willReturn($gtComp);
        $expr->orX($ltComp, $gtComp)->willReturn($or);

        $qb->andWhere($or)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT BETWEEN', ['2014-03-15', '2014-03-18']);
    }

    function it_throws_an_exception_if_value_is_not_a_string_an_array_or_a_datetime(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('release_date');

        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'release_date',
                'yyyy-mm-dd',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter',
                123
            )
        )->during('addAttributeFilter', [$attribute, '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('release_date');

        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'release_date',
                'yyyy-mm-dd',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter',
                'not a valid date format'
            )
        )->during('addAttributeFilter', [$attribute, '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings_or_dates(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('release_date');

        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'release_date',
                'yyyy-mm-dd',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter',
                123
            )
        )->during('addAttributeFilter', [$attribute, '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('release_date');

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'release_date',
                'should contain 2 strings with the format "yyyy-mm-dd"',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter',
                [123, 123, 'three']
            )
        )->during('addAttributeFilter', [$attribute, '>', [123, 123, 'three']]);
    }
}
