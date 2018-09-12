<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Datagrid\Filter;

use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;

/**
 * Product asset filter utility
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ProductAssetFilterUtility extends BaseFilterUtility implements TagFilterAwareInterface
{
    /** @var AssetRepositoryInterface */
    protected $repository;

    /**
     * @param AssetRepositoryInterface $repository
     */
    public function __construct(AssetRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
        if ('categories' === $field) {
            $this->repository->applyCategoriesFilter(
                $ds->getQueryBuilder(),
                $operator,
                $value
            );
        }
    }
}
