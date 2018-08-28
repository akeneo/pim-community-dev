<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface as PimDatasource;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AssetCategoryAccessSubscriberSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $accessRepository,
        AssetRepositoryInterface $assetRepository
    ) {
        $this->beConstructedWith($tokenStorage, $accessRepository, $assetRepository);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    public function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'oro_datagrid.datgrid.build.after.asset-grid'                => 'filter',
            'oro_datagrid.datgrid.build.after.asset-picker-grid'         => 'filter',
            'oro_datagrid.datgrid.build.after.product-asset-grid'        => 'filter',
            'oro_datagrid.datgrid.build.after.product-asset-picker-grid' => 'filter',
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
        PimDatasource $datasource,
        QueryBuilder $qb,
        TokenInterface $token,
        UserInterface $user,
        Collection $groupCollection,
        GroupInterface $group,
        Expr $expr,
        Expr\Func $func,
        Expr\Comparison $comparison,
        Expr\Andx $andx,
        Expr\Orx $orx
    ) {
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getQueryBuilder()->willReturn($qb);

        $tokenStorage->getToken()->willreturn($token);
        $token->getUser()->willReturn($user);
        $user->getGroups()->willReturn($groupCollection);
        $groupCollection->toArray()->willReturn([$group]);

        $accessRepository->getClassName()->willReturn(AssetCategoryAccess::class);
        $qb->getRootAlias()->willReturn('access');
        $qb->leftJoin('access.categories', 'asset_categories')->willReturn($qb);
        $qb->leftJoin(
            AssetCategoryAccess::class,
            'access',
            'WITH',
            'asset_categories.id = access.category'
        )->willReturn($qb);

        $qb->expr()->willReturn($expr);
        $expr->in('access.userGroup', ':groups')->willReturn($func);
        $expr->eq('access.viewItems', true)->willReturn($comparison);
        $expr->andX($func, $comparison)->willReturn($andx);
        $expr->isNull('asset_categories.id')->willReturn('asset_categories.id IS NULL');
        $expr->orX('asset_categories.id IS NULL', $andx)->willReturn($orx);

        $qb->andWhere($orx)->willReturn($qb);
        $qb->setParameter('groups', [$group])->willReturn($qb);

        $this->filter($event);
    }
}
