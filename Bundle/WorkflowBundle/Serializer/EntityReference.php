<?php

namespace Oro\Bundle\WorkflowBundle\Serializer;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;

class EntityReference
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var string
     */
    public $className;

    public function initByEntity(EntityManager $entityManager, $entity)
    {
        $metadata = $entityManager->getClassMetadata(get_class($entity));
        $this->className = $metadata->getName();
        $this->ids = $metadata->getIdentifierValues($entity);

        if (!$this->ids) {
            throw new WorkflowException(
                sprintf(
                    'Can\'t access id of entity "%s".'
                    . ' You must flush entity explicitly or set ID manually if you want to save it to workflow data.',
                    $this->className
                )
            );
        }
    }

    /**
     * Get class name.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Get ids.
     *
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * Set class name.
     *
     * @param string $className
     * @return EntityReference
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * Set ids.
     *
     * @param array $ids
     * @return EntityReference
     */
    public function setIds($ids)
    {
        $this->ids = $ids;
        return $this;
    }
}
