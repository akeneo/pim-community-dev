<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ProposalRepositoryInterface;

/**
 * Proposal filter utility
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalFilterUtility extends BaseFilterUtility
{
    /** @staticvar string */
    const PARENT_TYPE_KEY = 'parent_type';

    /** @var ProposalRepositoryInterface */
    protected $proposalRepository;

    /**
     * Constructor
     *
     * @param ProposalRepositoryInterface $proposalRepository
     */
    public function __construct(ProposalRepositoryInterface $proposalRepository)
    {
        $this->proposalRepository = $proposalRepository;
    }

    /**
     * Apply filter
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string $field
     * @param string $operator
     * @param mixed $value
     */
    public function applyFilter(FilterDatasourceAdapterInterface $ds, $field, $operator, $value)
    {
        $qb = $ds->getQueryBuilder();
        $this->proposalRepository->applyFilter($qb, $field, $operator, $value);
    }
}
