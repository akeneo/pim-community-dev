<?php

namespace Oro\Bundle\WorkflowBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;

class WorkflowDefinitionRepository extends EntityRepository
{
    /**
     * Get available workflow definitions for entity.
     *
     * @param object $entity
     * @return array
     */
    public function findWorkflowDefinitionsByEntity($entity)
    {
        $entityClass = ClassUtils::getRealClass(get_class($entity));
        return $this->findBy(array('managedEntityClass' => $entityClass));
    }
}
