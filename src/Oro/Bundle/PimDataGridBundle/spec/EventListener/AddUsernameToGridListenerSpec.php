<?php

namespace spec\Oro\Bundle\PimDataGridBundle\EventListener;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\RepositoryDatasource;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AddUsernameToGridListenerSpec extends ObjectBehavior
{
    function let(TokenStorageInterface $tokenStorage)
    {
        $this->beConstructedWith($tokenStorage);
    }

    function it_set_user_parameter_to_query_builder(
        BuildAfter $event,
        DatagridInterface $datagrid,
        RepositoryDatasource $datasource,
        QueryBuilder $queryBuilder,
        TokenInterface $token,
        UserInterface $user,
        Expr $expr,
        $tokenStorage
    ) {
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);

        $datasource->getParameters()->willReturn([]);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn($user);

        $datasource->setParameters(['user' => $user])->willReturn($queryBuilder);

        $datasource->getQueryBuilder()->willReturn($queryBuilder);

        $queryBuilder->expr()->willReturn($expr);
        $expr->eq(Argument::any(), Argument::any())->willReturn(Argument::any());

        $queryBuilder->andWhere(Argument::any())->shouldBeCalled();

        $this->onBuildAfter($event);
    }

    function it_set_user_parameter_to_null_if_token_is_null(
        BuildAfter $event,
        DatagridInterface $datagrid,
        RepositoryDatasource $datasource,
        QueryBuilder $queryBuilder,
        TokenInterface $token,
        UserInterface $user,
        Expr $expr,
        $tokenStorage
    ) {
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);

        $datasource->getParameters()->willReturn([]);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn(null);

        $datasource->setParameters(['user' => null])->willReturn($queryBuilder);

        $datasource->getQueryBuilder()->willReturn($queryBuilder);

        $queryBuilder->expr()->willReturn($expr);
        $expr->eq(Argument::any(), Argument::any())->willReturn(Argument::any());

        $queryBuilder->andWhere(Argument::any())->shouldBeCalled();

        $this->onBuildAfter($event);
    }
}
