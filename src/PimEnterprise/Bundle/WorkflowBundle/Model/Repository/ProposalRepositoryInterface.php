<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Proposal repository interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
