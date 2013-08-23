<?php

namespace Oro\Bundle\WorkflowBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;

class WorkflowDefinitionRepository extends EntityRepository
{
    /**
     * Get available workflow definitions for entity.
     *
     * @param object $entity
     * @return WorkflowDefinition[]
     */
    public function findWorkflowDefinitionsByEntity($entity)
    {
        $entityClasses = $this->getAllEntityClasses($entity);

        $queryBuilder = $this->createQueryBuilder('wd');
        $queryBuilder->innerJoin('wd.workflowDefinitionEntities', 'wde')
            ->where($queryBuilder->expr()->in('wde.className', $entityClasses));

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param object $entity
     * @return array
     */
    protected function getAllEntityClasses($entity)
    {
        $entityClass = ClassUtils::getRealClass(get_class($entity));

        $classes = array($entityClass);
        $classes = array_merge($classes, class_parents($entityClass));
        $classes = array_merge($classes, class_implements($entityClass));

        return array_values($classes);
    }
}
