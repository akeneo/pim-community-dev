<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Datagrid\EventListener;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProductCategoryAccessSubscriberSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        GetGrantedCategoryCodes $getAllViewableCategoryCodes
    ) {
        $this->beConstructedWith($tokenStorage, $getAllViewableCategoryCodes);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    public function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'oro_datagrid.datgrid.build.after.product-group-grid'              => 'filter',
            'oro_datagrid.datgrid.build.after.association-product-grid'        => 'filter',
            'oro_datagrid.datgrid.build.after.association-product-picker-grid' => 'filter',
            'oro_datagrid.datgrid.build.after.published-product-grid'          => 'filter',
        ]);
    }

    public function it_throws_an_exeception_when_used_with_bad_datasource(
        BuildAfter $event,
        DatagridInterface $datagrid,
        DatasourceInterface $datasource
    ) {
        $datagrid->getDatasource()->willReturn($datasource);
        $event->getDatagrid()->willReturn($datagrid);

        $this->shouldThrow(\RuntimeException::class)->during('filter', [$event]);
    }

    public function it_filters_datasource(
        $tokenStorage,
        GetGrantedCategoryCodes $getAllViewableCategoryCodes,
        BuildAfter $event,
        DatagridInterface $datagrid,
        ProductDatasource $datasource,
        ProductQueryBuilderInterface $pqb,
        TokenInterface $token,
        UserInterface $user
    ) {
        $datagrid->getDatasource()->willReturn($datasource);
        $event->getDatagrid()->willReturn($datagrid);
        $tokenStorage->getToken()->willreturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);
        $user->getGroupsIds()->willReturn([1,2]);
        $getAllViewableCategoryCodes->forGroupIds([1,2])->willReturn(['tees', 'sweats']);
        $datasource->getProductQueryBuilder()->willReturn($pqb);

        $pqb->addFilter('categories', 'IN OR UNCLASSIFIED', ['tees', 'sweats'], ['type_checking' => false])->shouldBeCalled();

        $this->filter($event);
    }
}
