<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\CategoryFilter;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\FilterBundle\Filter\Tag\TagFilterAwareInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Product asset filter utility
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ProductAssetFilterUtility extends BaseFilterUtility implements TagFilterAwareInterface
{
    /** @var AssetRepositoryInterface */
    protected $repository;

    /** @var CategoryFilter */
    protected $categoryFilter;

    /**
     * @param AssetRepositoryInterface $repository
     */
    public function __construct(AssetRepositoryInterface $repository, CategoryFilter $categoryFilter)
    {
        $this->repository     = $repository;
        $this->categoryFilter = $categoryFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function applyTagFilter(FilterDatasourceAdapterInterface $ds, $field, $operator, $value)
    {
        $this->repository->applyTagFilter($ds->getQueryBuilder(), $field, $operator, $value);
    }

    /**
     * Apply filter
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $field
     * @param string                           $operator
     * @param mixed                            $value
     */
    public function applyFilter(FilterDatasourceAdapterInterface $ds, $field, $operator, $value)
    {
        $qb = $ds->getQueryBuilder();

        if ('categories.id' === $field) {
            $this->categoryFilter->setQueryBuilder($qb);
            $this->categoryFilter->addFieldFilter($field, $operator, $value);
        }
    }
}
