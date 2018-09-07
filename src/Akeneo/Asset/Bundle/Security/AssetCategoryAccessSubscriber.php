<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Security;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
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
