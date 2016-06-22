<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class CompletenessFilterSpec extends ObjectBehavior
{
    function let(ChannelRepositoryInterface $channelRepository, Builder $qb)
    {
        $this->beConstructedWith($channelRepository, ['completeness'], ['=', '<', '>', '>=', '<=', '!=']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', '>=', '<=', '!=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_equals_filter_on_completeness_in_the_query($qb, Expr $expr, Expr $subExpr)
    {
        $qb->expr()->willReturn($subExpr, $expr);

        $expr->field('normalizedData.completenesses.mobile-en_US')
            ->shouldBeCalled()
            ->willReturn($expr);

        $expr->equals(100)
            ->shouldBeCalled()
            ->willReturn($expr);

        $subExpr->addOr($expr)->shouldBeCalled();
        $qb->addAnd($subExpr)->shouldBeCalled();

        $this->addFieldFilter('completeness', '=', 100, 'en_US', 'mobile');
    }

    function it_adds_a_not_equals_filter_on_completeness_in_the_query($qb, Expr $expr, Expr $subExpr)
    {
        $qb->expr()->willReturn($subExpr, $expr);
        $expr->field('normalizedData.completenesses.mobile-en_US')
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->exists(true)
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->notEqual(100)
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->addAnd($expr)
            ->shouldBeCalled()
            ->willReturn($expr);
        $subExpr->addOr($expr)->shouldBeCalled();
        $qb->addAnd($subExpr)->shouldBeCalled();

        $this->addFieldFilter('completeness', '!=', 100, 'en_US', 'mobile');
    }

    function it_adds_a_lower_than_filter_on_completeness_in_the_query($qb, Expr $expr, Expr $subExpr)
    {
        $qb->expr()->willReturn($subExpr, $expr);
        $expr->field('normalizedData.completenesses.mobile-en_US')
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->lt(100)
            ->shouldBeCalled()
            ->willReturn($expr);
        $subExpr->addOr($expr)->shouldBeCalled();
        $qb->addAnd($subExpr)->shouldBeCalled();

        $this->addFieldFilter('completeness', '<', 100, 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_filter_on_completeness_in_the_query($qb, Expr $expr, Expr $subExpr)
    {
        $qb->expr()->willReturn($subExpr, $expr);
        $expr->field('normalizedData.completenesses.mobile-en_US')
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->gt(55)
            ->shouldBeCalled()
            ->willReturn($expr);
        $subExpr->addOr($expr)->shouldBeCalled();
        $qb->addAnd($subExpr)->shouldBeCalled();

        $this->addFieldFilter('completeness', '>', 55, 'en_US', 'mobile');
    }

    function it_adds_a_greater_or_equal_than_filter_on_completeness_in_the_query($qb, Expr $expr, Expr $subExpr)
    {
        $qb->expr()->willReturn($subExpr, $expr);
        $expr->field('normalizedData.completenesses.mobile-en_US')
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->gte(55)
            ->shouldBeCalled()
            ->willReturn($expr);
        $subExpr->addOr($expr)->shouldBeCalled();
        $qb->addAnd($subExpr)->shouldBeCalled();

        $this->addFieldFilter('completeness', '>=', 55, 'en_US', 'mobile');
    }

    function it_adds_a_lower_or_equal_than_filter_on_completeness_in_the_query($qb, Expr $expr, Expr $subExpr)
    {
        $qb->expr()->willReturn($subExpr, $expr);
        $expr->field('normalizedData.completenesses.mobile-en_US')
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->lte(60)
            ->shouldBeCalled()
            ->willReturn($expr);
        $subExpr->addOr($expr)->shouldBeCalled();
        $qb->addAnd($subExpr)->shouldBeCalled();

        $this->addFieldFilter('completeness', '<=', 60, 'en_US', 'mobile');
    }

    function it_filters_on_completeness_on_any_locale(
        $channelRepository,
        $qb,
        ChannelInterface $channel,
        Expr $expr,
        Expr $subExpr
    ) {
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn(['en_US', 'fr_FR']);

        $qb->expr()->willReturn($subExpr, $expr);

        $expr->field('normalizedData.completenesses.mobile-en_US')
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->lte(60)
            ->shouldBeCalled()
            ->willReturn($expr);

        $expr->field('normalizedData.completenesses.mobile-fr_FR')
            ->shouldBeCalled()
            ->willReturn($expr);
        $expr->lte(60)
            ->shouldBeCalled()
            ->willReturn($expr);

        $subExpr->addOr($expr)->shouldBeCalledTimes(2);
        $qb->addAnd($subExpr)->shouldBeCalled();

        $this->addFieldFilter('completeness', '<=', 60, null, 'mobile');
    }

    function it_throws_an_exception_when_scope_is_not_provided()
    {
        $this
            ->shouldThrow('Pim\Component\Catalog\Exception\InvalidArgumentException')
            ->duringAddFieldFilter('completeness', '=', 100);
        $this
            ->shouldThrow('Pim\Component\Catalog\Exception\InvalidArgumentException')
            ->duringAddFieldFilter('completeness', '=', 100, 'fr_FR', null);
    }

    function it_throws_an_exception_if_value_is_not_an_integer()
    {
        $this->shouldThrow(
            InvalidArgumentException::numericExpected('completeness', 'filter', 'completeness', gettype('123'))
        )->during('addFieldFilter', ['completeness', '=', '12a3', 'fr_FR', 'mobile']);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['completeness']);
    }
}
