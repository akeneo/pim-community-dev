<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProductCategoryAccessSubscriberSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $accessRepository
    ) {
        $this->beConstructedWith($tokenStorage, $accessRepository);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    public function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'oro_datagrid.datgrid.build.after.product-group-grid'              => 'filter',
            'oro_datagrid.datgrid.build.after.association-product-grid'        => 'filter',
            'oro_datagrid.datgrid.build.after.product-grid'                    => 'filter',
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

        $this->shouldThrow('\RuntimeException')->during('filter', [$event]);
    }

    public function it_filters_datasource(
        $tokenStorage,
        $accessRepository,
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
        $accessRepository->getGrantedCategoryCodes($user, Attributes::VIEW_ITEMS)->willReturn(['tees', 'sweats']);
        $datasource->getProductQueryBuilder()->willReturn($pqb);

        $pqb->addFilter('categories', 'IN OR UNCLASSIFIED', ['tees', 'sweats'])->shouldBeCalled();

        $this->filter($event);
    }
}
