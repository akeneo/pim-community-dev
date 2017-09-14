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
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Component\Catalog\Query\Filter\Operators;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Apply permissions on the products data grids filtering them by granted categories.
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ProductCategoryAccessSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var CategoryAccessRepository */
    protected $accessRepository;

    /**
     * @param TokenStorageInterface    $tokenStorage
     * @param CategoryAccessRepository $accessRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $accessRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->accessRepository = $accessRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'oro_datagrid.datgrid.build.after.product-group-grid'         => 'filter',
            'oro_datagrid.datgrid.build.after.association-product-grid'   => 'filter',
            'oro_datagrid.datgrid.build.after.product-grid'               => 'filter',
            'oro_datagrid.datgrid.build.after.published-product-grid'     => 'filter',
        ];
    }

    /**
     * @param BuildAfter $event
     */
    public function filter(BuildAfter $event)
    {
        $dataSource = $event->getDatagrid()->getDatasource();

        if (!$dataSource instanceof ProductDatasource) {
            throw new \RuntimeException(sprintf(
                'Product category permissions can be applied only on products datasources, "%s" given',
                get_class($dataSource)
            ));
        }

        $grantedCategories = $this->accessRepository->getGrantedCategoryCodes(
            $this->tokenStorage->getToken()->getUser(),
            Attributes::VIEW_ITEMS
        );

        $dataSource->getProductQueryBuilder()->addFilter(
            'categories',
            Operators::IN_LIST_OR_UNCLASSIFIED,
            $grantedCategories
        );
    }
}
