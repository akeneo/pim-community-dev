<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Proposal ODM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProposalRepository extends DocumentRepository
{
    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder($productId)
    {
        return $this
            ->createQueryBuilder('p')
            ->field('product')->equals($productId);
    }
}
