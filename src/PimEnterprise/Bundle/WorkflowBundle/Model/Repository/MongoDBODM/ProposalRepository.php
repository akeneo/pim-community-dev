<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Proposal ODM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalRepository extends DocumentRepository
{
    /**
     * @param string $productId
     *
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        return $this
            ->createQueryBuilder('p')
//             ->field('product')->equals($productId);*
        ;
    }
}
