<?php

namespace Oro\Bundle\WorkflowBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;

class WorkflowItemRepository extends EntityRepository
{
    /**
     * Get workflow items associated with entity.
     *
     * @param object $entity
     * @return array
     */
    public function findWorkflowItemsByEntity($entity)
    {
        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $metadata = $this->getEntityManager()->getClassMetadata($entityClass);
        $entityId = WorkflowBindEntity::convertIdentifiersToString($metadata->getIdentifierValues($entity));

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('wi')
            ->from('OroWorkflowBundle:WorkflowBindEntity', 'wbe')
            ->innerJoin('OroWorkflowBundle:WorkflowItem', 'wi', 'WITH', 'wi = wbe.workflowItem')
            ->where('wbe.entityClass = :entityClass')
            ->andWhere('wbe.entityId = :entityId')
            ->setParameter('entityClass', $entityClass)
            ->setParameter('entityId', $entityId);

        return $qb->getQuery()->getResult();
    }
}
