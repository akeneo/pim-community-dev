<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\DataGrid\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
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

    /**
     * @param AssetRepositoryInterface $repository
     */
    public function __construct(AssetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Apply tag filter
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $field
     * @param string                           $operator
     * @param mixed                            $value
     */
    public function applyTagFilter(FilterDatasourceAdapterInterface $ds, $field, $operator, $value)
    {
        $this->repository->applyTagFilter($ds->getQueryBuilder(), $field, $operator, $value);
    }
}
