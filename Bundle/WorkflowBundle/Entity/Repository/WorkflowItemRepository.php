<?php

namespace Oro\Bundle\WorkflowBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;

class WorkflowItemRepository extends EntityRepository
{
    /**
     * Get workflow items associated with entity.
     *
     * @param string $entityClass
     * @param string|array $entityIdentifier
     * @return array
     */
    public function findByEntityMetadata($entityClass, $entityIdentifier)
    {
        $entityIdentifierString = WorkflowBindEntity::convertIdentifiersToString($entityIdentifier);

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('wi')
            ->from('OroWorkflowBundle:WorkflowBindEntity', 'wbe')
            ->innerJoin('OroWorkflowBundle:WorkflowItem', 'wi', 'WITH', 'wi = wbe.workflowItem')
            ->where('wbe.entityClass = :entityClass')
            ->andWhere('wbe.entityId = :entityId')
            ->setParameter('entityClass', $entityClass)
            ->setParameter('entityId', $entityIdentifierString);

        return $qb->getQuery()->getResult();
    }
}
