<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;

/**
 * Proposition filter utility
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionFilterUtility extends BaseFilterUtility
{
    /** @staticvar string */
    const PARENT_TYPE_KEY = 'parent_type';

    /** @var PropositionRepositoryInterface */
    protected $proposalRepository;

    /**
     * Constructor
     *
     * @param PropositionRepositoryInterface $propositionRepository
     */
    public function __construct(PropositionRepositoryInterface $proposalRepository)
    {
        $this->proposalRepository = $proposalRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getParamMap()
    {
        return [self::PARENT_TYPE_KEY => self::TYPE_KEY];
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
        $this->proposalRepository->applyFilter($ds->getQueryBuilder(), $field, $operator, $value);
    }
}
