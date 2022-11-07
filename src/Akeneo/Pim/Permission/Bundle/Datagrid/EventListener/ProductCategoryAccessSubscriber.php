<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid\EventListener;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

/**
 * Apply permissions on the products data grids filtering them by granted categories.
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ProductCategoryAccessSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var array */
    protected $grantedCategoryIdsPerUser;

    /** @var array */
    protected $grantedCategoryCodesPerUser;

    /** @var GetGrantedCategoryCodes */
    private $getAllViewableCategoryCodes;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        GetGrantedCategoryCodes $getAllViewableCategoryCodes
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->grantedCategoryCodesPerUser = [];
        $this->getAllViewableCategoryCodes = $getAllViewableCategoryCodes;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'oro_datagrid.datgrid.build.after.product-group-grid'              => 'filter',
            'oro_datagrid.datgrid.build.after.association-product-grid'        => 'filter',
            'oro_datagrid.datgrid.build.after.association-product-picker-grid' => 'filter',
            'oro_datagrid.datgrid.build.after.published-product-grid'          => 'filter',
        ];
    }

    /**
     * @param BuildAfter $event
     */
    public function filter(BuildAfter $event)
    {
        $dataSource = $event->getDatagrid()->getDatasource();

        if (!$dataSource instanceof ProductDatasource && !$dataSource instanceof ProductAndProductModelDatasource) {
            throw new \RuntimeException(sprintf(
                'Product category permissions can be applied only on products datasources, "%s" given',
                get_class($dataSource)
            ));
        }

        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        $userId = $user->getId();

        if (!isset($this->grantedCategoryCodesPerUser[$userId])) {
            $this->grantedCategoryCodesPerUser[$userId] = $this->getAllViewableCategoryCodes->forGroupIds($user->getGroupsIds());
        }

        $grantedCategories = $this->grantedCategoryCodesPerUser[$userId];

        $dataSource->getProductQueryBuilder()->addFilter(
            'categories',
            Operators::IN_LIST_OR_UNCLASSIFIED,
            $grantedCategories,
            ['type_checking' => false]
        );
    }
}
