<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetLastOperationsSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_a_last_operations_query()
    {
        $this->shouldImplement(GetLastOperationsInterface::class);
    }

    function it_executes_the_query_builder(
        $connection,
        UserInterface $user,
        QueryBuilder $qb,
        ExpressionBuilder $expr,
        Result $result
    ) {
        $lastOperations = ['an_operation', 'another_one'];
        $connection->createQueryBuilder()->willReturn($qb);
        $user->getUserIdentifier()->shouldBeCalled()->willReturn('julia');

        $qb->select(Argument::cetera())->willReturn($qb);
        $qb->from(Argument::cetera())->willReturn($qb);
        $qb->innerJoin(Argument::cetera())->willReturn($qb);
        $qb->leftJoin(Argument::cetera())->willReturn($qb);
        $qb->expr()->willReturn($expr);
        $qb->where(Argument::cetera())->willReturn($qb);
        $qb->andWhere(Argument::cetera())->willReturn($qb);
        $qb->groupBy(Argument::cetera())->willReturn($qb);
        $qb->orderBy(Argument::cetera())->willReturn($qb);
        $qb->setMaxResults(Argument::cetera())->willReturn($qb);
        $qb->setParameters(Argument::cetera())->willReturn($qb);

        $expr->eq(Argument::cetera())->willReturn('eq');
        $expr->notIn(Argument::cetera())->willReturn('notIn');


        $qb->execute()->willReturn($result);
        $result->fetchAllAssociative()->willReturn($lastOperations);

        $this->execute($user)->shouldReturn($lastOperations);
    }

    function it_returns_the_query_builder(
        $connection,
        UserInterface $user,
        QueryBuilder $qb,
        ExpressionBuilder $expr
    ) {
        $connection->createQueryBuilder()->willReturn($qb);
        $user->getUserIdentifier()->willReturn('julia');

        $qb->select(Argument::cetera())->willReturn($qb);
        $qb->from(Argument::cetera())->willReturn($qb);
        $qb->innerJoin(Argument::cetera())->willReturn($qb);
        $qb->leftJoin(Argument::cetera())->willReturn($qb);
        $qb->expr()->willReturn($expr);
        $qb->where(Argument::cetera())->willReturn($qb);
        $qb->andWhere(Argument::cetera())->willReturn($qb);
        $qb->groupBy(Argument::cetera())->willReturn($qb);
        $qb->orderBy(Argument::cetera())->willReturn($qb);
        $qb->setMaxResults(Argument::cetera())->willReturn($qb);
        $qb->setParameters(Argument::cetera())->willReturn($qb);

        $expr->isNull(Argument::cetera())->willReturn($expr);
        $expr->orX(Argument::cetera())->willReturn($expr);
        $expr->eq(Argument::cetera())->willReturn('eq');
        $expr->notIn(Argument::cetera())->willReturn('notIn');

        $this->getQueryBuilder($user)->shouldReturn($qb);
    }
}
