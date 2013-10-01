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
     * @param string|null $workflowName
     * @param string|null $workflowType
     * @return array
     */
    public function findByEntityMetadata($entityClass, $entityIdentifier, $workflowName = null, $workflowType = null)
    {
        $entityIdentifierString = WorkflowBindEntity::convertIdentifiersToString($entityIdentifier);

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('wi')
            ->from('OroWorkflowBundle:WorkflowItem', 'wi')
            ->innerJoin('wi.bindEntities', 'wbe')
            ->where('wbe.entityClass = :entityClass')
            ->andWhere('wbe.entityId = :entityId')
            ->setParameter('entityClass', $entityClass)
            ->setParameter('entityId', $entityIdentifierString);

        if ($workflowName) {
            $qb->andWhere('wi.workflowName = :workflowName')
                ->setParameter('workflowName', $workflowName);
        }

        if ($workflowType) {
            $qb->innerJoin('wi.definition', 'wd')
                ->andWhere('wd.type = :workflowType')
                ->setParameter('workflowType', $workflowType);
        }

        return $qb->getQuery()->getResult();
    }
}
