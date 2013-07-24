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
        $entityClass = ClassUtils::getRealClass(get_class($entity));
        return $this->findBy(array('managedEntityClass' => $entityClass));
    }
}
