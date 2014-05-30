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
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        return $this
            ->createQueryBuilder('p');
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function applyFilter($qb, $field, $operator, $value)
    {
        if ('IN' === $operator) {
            if (!empty($value)) {
                $qb->field($field)->in($value);
            }
        }
    }
}
