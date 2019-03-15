<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Persistence\Sql;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetLastOperationsSpec extends ObjectBehavior
{
    function let(GetLastOperationsInterface $getLastOperations)
    {
        $this->beConstructedWith($getLastOperations);
    }

    function it_is_a_last_operations_query()
    {
        $this->shouldImplement(GetLastOperationsInterface::class);
    }

    function it_executes_the_query_builder(
        $getLastOperations,
        UserInterface $user,
        QueryBuilder $qb,
        ExpressionBuilder $expr,
        Statement $statement
    ) {
        $lastOperations = ['an_operation', 'another_one'];
        $getLastOperations->getQueryBuilder($user)->willReturn($qb);
        $user->getGroupsIds()->willReturn(['1', '42']);

        $qb->innerJoin(Argument::cetera())->willReturn($qb);
        $qb->expr()->willReturn($expr);
        $qb->andWhere(Argument::cetera())->willReturn($qb);
        $qb->setParameter(Argument::cetera())->willReturn($qb);

        $expr->in(Argument::cetera())->willReturn('in');
        $expr->eq(Argument::cetera())->willReturn('eq');

        $qb->execute()->willReturn($statement);
        $statement->fetchAll()->willReturn($lastOperations);

        $this->execute($user)->shouldReturn($lastOperations);
    }

    function it_returns_the_query_builder(
        $getLastOperations,
        UserInterface $user,
        QueryBuilder $qb,
        ExpressionBuilder $expr
    ) {
        $getLastOperations->getQueryBuilder($user)->willReturn($qb);
        $user->getGroupsIds()->willReturn(['1', '42']);

        $qb->innerJoin(Argument::cetera())->willReturn($qb);
        $qb->expr()->willReturn($expr);
        $qb->andWhere(Argument::cetera())->willReturn($qb);
        $qb->setParameter(Argument::cetera())->willReturn($qb);

        $expr->in(Argument::cetera())->willReturn('in');
        $expr->eq(Argument::cetera())->willReturn('eq');

        $this->getQueryBuilder($user)->shouldReturn($qb);
    }
}
