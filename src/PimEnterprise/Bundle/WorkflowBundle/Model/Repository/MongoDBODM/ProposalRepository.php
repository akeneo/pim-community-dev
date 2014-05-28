<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\Repository\ProposalRepositoryInterface;

/**
 * Proposal ODM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalRepository extends DocumentRepository implements ProposalRepositoryInterface
{
    /**
     * Create datagrid query builder
     *
     * @param array $params
     *
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function createDatagridQueryBuilder(array $params = array())
    {
        $qb = $this->createQueryBuilder('p');

        if (isset($params['product'])) {
            $qb->field('product')->equals($params['product']);
        }

        return $this
            ->createQueryBuilder('p');
    }
}
