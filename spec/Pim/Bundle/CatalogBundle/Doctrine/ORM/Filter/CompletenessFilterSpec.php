<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\EntityManager;
use \Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

class CompletenessFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface');
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
        $expr->eq('filterCompleteness.ratio', '100')
            ->shouldBeCalled()
            ->willReturn($comparison);
        $this->setQueryBuilder($qb);
        $qb->leftJoin(
            'PimCatalogBundle:Locale',
            'filterCompletenessLocale',
            'WITH',
            'filterCompletenessLocale'.'.code = :dataLocale'
        )->shouldBeCalled()->willReturn($qb);
        $qb->leftJoin(
            'PimCatalogBundle:Channel',
            'filterCompletenessChannel',
            'WITH',
            'filterCompletenessChannel'.'.code = :scopeCode'
        )->shouldBeCalled()->willReturn($qb);
        $qb->leftJoin(
            'test',
            'filterCompleteness',
            'WITH',
            'filterCompleteness.locale = filterCompletenessLocale.id AND filterCompleteness.channel = filterCompletenessChannel.id AND filterCompleteness.product = p.id'
        )->shouldBeCalled()->willReturn($qb);

        $qb->andWhere('filterCompleteness.ratio = 100')->shouldBeCalled();

        $this->addFieldFilter('completeness', '=', '100');
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
        $expr->lt('filterCompleteness.ratio', '100')
            ->shouldBeCalled()
            ->willReturn($comparison);
        $this->setQueryBuilder($qb);
        $qb->leftJoin(
            'PimCatalogBundle:Locale',
            'filterCompletenessLocale',
            'WITH',
            'filterCompletenessLocale'.'.code = :dataLocale'
        )->shouldBeCalled()->willReturn($qb);
        $qb->leftJoin(
            'PimCatalogBundle:Channel',
            'filterCompletenessChannel',
            'WITH',
            'filterCompletenessChannel'.'.code = :scopeCode'
        )->shouldBeCalled()->willReturn($qb);
        $qb->leftJoin(
            'test',
            'filterCompleteness',
            'WITH',
            'filterCompleteness.locale = filterCompletenessLocale.id AND filterCompleteness.channel = filterCompletenessChannel.id AND filterCompleteness.product = p.id'
        )->shouldBeCalled()->willReturn($qb);

        $qb->andWhere('filterCompleteness.ratio < 100')->shouldBeCalled();

        $this->addFieldFilter('completeness', '<', '100');
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('completeness')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }
}
