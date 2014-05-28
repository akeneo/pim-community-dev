<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use PimEnterprise\Bundle\WorkflowBundle\Model\Repository\ProposalRepositoryInterface;

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
}
