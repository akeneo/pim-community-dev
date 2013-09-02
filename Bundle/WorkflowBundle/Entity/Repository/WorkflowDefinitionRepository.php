<?php

namespace Oro\Bundle\WorkflowBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;

class WorkflowDefinitionRepository extends EntityRepository
{
    /**
     * Get available workflow definitions for entity class
     *
     * @param string $entityClass
     * @param string|null $workflowName
     * @return WorkflowDefinition[]
     */
    public function findByEntityClass($entityClass, $workflowName = null)
    {
        $entityClasses = $this->getAllEntityClasses($entityClass);

        $queryBuilder = $this->createQueryBuilder('wd');
        $queryBuilder->innerJoin('wd.workflowDefinitionEntities', 'wde')
            ->where($queryBuilder->expr()->in('wde.className', $entityClasses));

        if ($workflowName) {
            $queryBuilder->andWhere('wd.name = :workflowName')
                ->setParameter('workflowName', $workflowName);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param string $entityClass
     * @return array
     */
    protected function getAllEntityClasses($entityClass)
    {
        $classes = array($entityClass);
        $classes = array_merge($classes, class_parents($entityClass));
        $classes = array_merge($classes, class_implements($entityClass));

        return array_values($classes);
    }
}
