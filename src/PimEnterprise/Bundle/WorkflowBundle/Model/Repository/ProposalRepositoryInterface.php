<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Proposal repository interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ProposalRepositoryInterface extends ObjectRepository
{
    /**
     * Create datagrid query builder
     *
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder();
}
