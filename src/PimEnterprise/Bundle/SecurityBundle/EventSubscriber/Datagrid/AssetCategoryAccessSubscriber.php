<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Apply permissions on the assets data grids filtering them by granted categories.
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class AssetCategoryAccessSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var CategoryAccessRepository */
    protected $accessRepository;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /**
     * @todo merge: $assetRepository is useless now, should be removed in master
     *
     * @param TokenStorageInterface    $tokenStorage
     * @param CategoryAccessRepository $accessRepository
     * @param AssetRepositoryInterface $assetRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $accessRepository,
        AssetRepositoryInterface $assetRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->accessRepository = $accessRepository;
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'oro_datagrid.datgrid.build.after.asset-grid'                => 'filter',
            'oro_datagrid.datgrid.build.after.asset-picker-grid'         => 'filter',
            'oro_datagrid.datgrid.build.after.product-asset-grid'        => 'filter',
            'oro_datagrid.datgrid.build.after.product-asset-picker-grid' => 'filter',
        ];
    }

    /**
     * @param BuildAfter $event
     */
    public function filter(BuildAfter $event)
    {
        $dataSource = $event->getDatagrid()->getDatasource();

        if (!$dataSource instanceof DatasourceInterface) {
            throw new \RuntimeException(sprintf(
                'Asset category permissions can be applied only on pim datasources, "%s" given',
                get_class($dataSource)
            ));
        }

        $this->applyFilterByCategoryIdsOrUnclassified(
            $dataSource->getQueryBuilder(),
            $this->tokenStorage->getToken()->getUser()
        );
    }

    /**
     * @param QueryBuilder  $qb
     * @param UserInterface $user
     */
    private function applyFilterByCategoryIdsOrUnclassified(QueryBuilder $qb, UserInterface $user)
    {
        $qb->leftJoin($qb->getRootAlias() . '.categories', 'asset_categories');
        $qb->leftJoin(
            $this->accessRepository->getClassName(),
            'access',
            Join::WITH,
            'asset_categories.id = access.category'
        );

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull('asset_categories.id'),
                $qb->expr()->andX(
                    $qb->expr()->in('access.userGroup', ':groups'),
                    $qb->expr()->eq('access.viewItems', true)
                )
            )
        );

        $qb->setParameter('groups', $user->getGroups()->toArray());
    }
}
