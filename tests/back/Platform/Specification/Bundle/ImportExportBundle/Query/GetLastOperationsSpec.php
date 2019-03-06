<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Registry\NotVisibleJobsRegistry;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetLastOperationsSpec extends ObjectBehavior
{
    function let(Connection $connection, NotVisibleJobsRegistry $notVisibleJobs)
    {
        $this->beConstructedWith($connection, $notVisibleJobs);
    }

    function it_is_a_last_operations_query()
    {
        $this->shouldImplement(GetLastOperationsInterface::class);
    }

    function it_executes_the_query_builder(
        $connection,
        $notVisibleJobs,
        UserInterface $user,
        QueryBuilder $qb,
        ExpressionBuilder $expr,
        Statement $statement
    ) {
        $lastOperations = ['an_operation', 'another_one'];
        $notVisibleJobs->getCodes()->willReturn(['not_visible_job', 'again']);
        $connection->createQueryBuilder()->willReturn($qb);
        $user->getUsername()->willReturn('julia');

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

        $qb->execute()->willReturn($statement);
        $statement->fetchAll()->willReturn($lastOperations);

        $this->execute($user)->shouldReturn($lastOperations);
    }

    function it_returns_the_query_builder(
        $notVisibleJobs,
        $connection,
        UserInterface $user,
        QueryBuilder $qb,
        ExpressionBuilder $expr
    ) {
        $notVisibleJobs->getCodes()->willReturn(['not_visible_job', 'again']);
        $connection->createQueryBuilder()->willReturn($qb);
        $user->getUsername()->willReturn('julia');

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

        $this->getQueryBuilder($user)->shouldReturn($qb);
    }
}
