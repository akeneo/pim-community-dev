<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;

/**
 * Proposition filter utility
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionFilterUtility extends BaseFilterUtility
{
    /** @var PropositionRepositoryInterface */
    protected $repository;

    /**
     * Constructor
     *
     * @param PropositionRepositoryInterface $repository
     */
    public function __construct(PropositionRepositoryInterface $repository)
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
