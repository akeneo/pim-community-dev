<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
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
            'oro_datagrid.datgrid.build.after.product-variant-group-grid' => 'filter',
            'oro_datagrid.datgrid.build.after.product-group-grid'         => 'filter',
            'oro_datagrid.datgrid.build.after.association-product-grid'   => 'filter',
            'oro_datagrid.datgrid.build.after.product-grid'               => 'filter',
            'oro_datagrid.datgrid.build.after.published-product-grid'     => 'filter',
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
        $accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS)->willReturn([2, 3]);
        $datasource->getProductQueryBuilder()->willReturn($pqb);

        $pqb->addFilter('categories.id', 'IN OR UNCLASSIFIED', [2, 3])->shouldBeCalled();

        $this->filter($event);
    }
}
