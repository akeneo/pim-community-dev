<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;

/**
 * ProductDraft filter utility
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductDraftFilterUtility extends BaseFilterUtility
{
    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /**
     * Constructor
     *
     * @param ProductDraftRepositoryInterface $repository
     */
    public function __construct(ProductDraftRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
        $this->repository->applyFilter($ds->getQueryBuilder(), $field, $operator, $value);
    }
}
