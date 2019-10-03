<?php

namespace spec\Oro\Bundle\PimDataGridBundle\EventListener;

use Akeneo\Platform\Bundle\ImportExportBundle\Registry\NotVisibleJobsRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\PimDataGridBundle\Datasource\Datasource;
use Oro\Bundle\PimDataGridBundle\EventListener\BlackListJobsFromGridListener;
use PhpSpec\ObjectBehavior;

class BlackListJobsFromGridListenerSpec extends ObjectBehavior
{
    public function let(NotVisibleJobsRegistry $notVisibleJobsRegistry): void
    {
        $this->beConstructedWith($notVisibleJobsRegistry);
    }

    public function it_is_a_listener()
    {
        $this->shouldHaveType(BlackListJobsFromGridListener::class);
    }

    public function it_black_lists_job_codes_from_datagrid_query(
        $notVisibleJobsRegistry,
        BuildAfter $event,
        DatagridInterface $datagrid,
        Datasource $datasource,
        QueryBuilder $queryBuilder,
        Expr $expr,
        Expr\Func $func
    ) {
        $notVisibleJobsRegistry->getCodes()->willReturn([
            'refresh_project_completeness_calculation',
        ]);
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getParameters()->willReturn(['this_is' => 'a parameter']);
        $datasource->setParameters(
            [
                'this_is' => 'a parameter',
                'blackListedJobCodes' => [
                    'refresh_project_completeness_calculation',
                ]
            ]
        )->shouldBeCalled();

        $datasource->getQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->expr()->willReturn($expr);
        $expr
            ->notIn('j.code', ':blackListedJobCodes')
            ->shouldBeCalled()
            ->willReturn($func);
        $queryBuilder->andWhere($func)->shouldBeCalled();

        $this->onBuildAfter($event);
    }
}
