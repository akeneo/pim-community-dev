<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Prophecy\Argument;

class CompletenessFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith(['completeness'], ['=', '<']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_equal_filter_on_a_field_in_the_query(
        QueryBuilder $qb,
        Expr $expr,
        EntityManager $em,
        ClassMetadata $cm,
        Expr\Comparison $comparison
    ) {
        $qb->expr()->willReturn($expr);
        $qb->getRootAlias()->willReturn('p');
        $qb->getRootEntities()->willReturn([]);
        $qb->getEntityManager()->willReturn($em);
        $em->getClassMetadata(false)->willReturn($cm);
        $comparison->__toString()->willReturn('filterCompleteness.ratio = 100');
        $cm->getAssociationMapping('completenesses')->willReturn(['targetEntity' => 'test']);
        $expr->literal('100')
            ->willReturn('100');
        $expr->eq(Argument::any(), '100')
            ->willReturn($comparison);
        $this->setQueryBuilder($qb);
        $qb->leftJoin(
            'PimCatalogBundle:Locale',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->leftJoin(
            'PimCatalogBundle:Channel',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->leftJoin(
            'test',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->setParameter('cLocaleCode', Argument::any())->willReturn($qb);
        $qb->setParameter('cScopeCode', Argument::any())->willReturn($qb);

        $qb->andWhere('filterCompleteness.ratio = 100')->shouldBeCalled();

        $this->addFieldFilter('completeness', '=', 100, 'en_US', 'ecommerce');
    }

    function it_adds_a_filter_on_a_field_in_the_query(
        QueryBuilder $qb,
        Expr $expr,
        EntityManager $em,
        ClassMetadata $cm,
        Expr\Comparison $comparison
    ) {
        $qb->expr()->willReturn($expr);
        $qb->getRootAlias()->willReturn('p');
        $qb->getRootEntities()->willReturn([]);
        $qb->getEntityManager()->willReturn($em);
        $em->getClassMetadata(false)->willReturn($cm);
        $comparison->__toString()->willReturn('filterCompleteness.ratio < 100');
        $cm->getAssociationMapping('completenesses')->willReturn(['targetEntity' => 'test']);
        $expr->literal('100')
            ->willReturn('100');
        $expr->lt(Argument::any(), '100')
            ->willReturn($comparison);
        $this->setQueryBuilder($qb);
        $qb->leftJoin(
            'PimCatalogBundle:Locale',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->leftJoin(
            'PimCatalogBundle:Channel',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->leftJoin(
            'test',
            Argument::any(),
            'WITH',
            Argument::any()
        )->willReturn($qb);
        $qb->setParameter('cLocaleCode', Argument::any())->willReturn($qb);
        $qb->setParameter('cScopeCode', Argument::any())->willReturn($qb);

        $qb->andWhere('filterCompleteness.ratio < 100')->shouldBeCalled();

        $this->addFieldFilter('completeness', '<', 100, 'en_US', 'mobile');
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('completeness')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }

    function it_throws_an_exception_if_value_is_not_a_integer()
    {
        $this->shouldThrow(InvalidArgumentException::numericExpected('completeness', 'filter', 'completeness', gettype('123')))
            ->during('addFieldFilter', ['completeness', '=', '12z3', 'en_US', 'mobile']);
    }
}
