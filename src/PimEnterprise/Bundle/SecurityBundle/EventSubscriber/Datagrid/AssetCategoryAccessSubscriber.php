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

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
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

        $grantedCategories = $this->accessRepository->getGrantedCategoryCodes(
            $this->tokenStorage->getToken()->getUser(),
            Attributes::VIEW_ITEMS
        );

        $this->assetRepository->applyCategoriesFilter(
            $dataSource->getQueryBuilder(),
            Operators::IN_LIST_OR_UNCLASSIFIED,
            $grantedCategories
        );
    }
}
