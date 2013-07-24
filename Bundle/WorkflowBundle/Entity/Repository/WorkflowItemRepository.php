<?php

namespace Oro\Bundle\WorkflowBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;

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
        $idField = $metadata->getSingleIdentifierFieldName();
        $entityIdValues = $metadata->getIdentifierValues($entity);
        $entityId = $entityIdValues[$idField];

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('wi')
            ->from('OroWorkflowBundle:WorkflowItemEntity', 'we')
            ->innerJoin('OroWorkflowBundle:WorkflowItem', 'wi', 'WITH', 'wi = we.workflowItem')
            ->where('we.entityClass = :entityClass')
            ->andWhere('we.entityId = :entityId')
            ->setParameter('entityClass', $entityClass)
            ->setParameter('entityId', $entityId);

        return $qb->getQuery()->getResult();
    }
}
