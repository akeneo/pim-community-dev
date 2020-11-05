<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface as PimFilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Webmozart\Assert\Assert;

/**
 * ProductDraft filter utility
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductDraftFilterUtility extends BaseFilterUtility
{
    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $repository;

    /**
     * Constructor
     *
     * @param EntityWithValuesDraftRepositoryInterface $repository
     */
    public function __construct(EntityWithValuesDraftRepositoryInterface $repository)
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
    public function applyFilter(FilterDatasourceAdapterInterface $ds, string $field, string $operator, $value)
    {
        if ($ds instanceof FilterProductDatasourceAdapterInterface) {
            $ds->getProductQueryBuilder()->addFilter($field, $operator, $value);
        } else {
            Assert::implementsInterface($ds, PimFilterDatasourceAdapterInterface::class);
            $this->repository->applyFilter($ds->getQueryBuilder(), $field, $operator, $value);
        }
    }
}
