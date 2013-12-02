<?php

namespace Oro\Bundle\ImportExportBundle\Writer;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Finds detached properties in entity and reloads them from UnitOfWork.
 *
 * After EntityManager::clear method called some entities could be in detached state,
 * for example User in SecurityContext.
 */
class EntityDetachFixer
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Finds detached properties in entity and reloads them from UnitOfWork
     *
     * @param object $entity doctrine entity
     * @param int $level maximum nesting level
     */
    public function fixEntityAssociationFields($entity, $level = 0)
    {
        if ($level < 0) {
            return;
        }

        $metadata = $this->entityManager->getClassMetadata(get_class($entity));
        foreach ($metadata->getAssociationMappings() as $associationMapping) {
            $fieldName = $associationMapping['fieldName'];
            $value = PropertyAccess::createPropertyAccessor()->getValue($entity, $fieldName);
            if ($value && is_object($value)) {
                if ($value instanceof Collection) {
                    $this->fixCollectionField($value, $level);
                } else {
                    $this->fixEntityField($entity, $fieldName, $value, $level);
                }
            }
        }
    }

    /**
     * @param Collection $collection
     * @param $level
     */
    protected function fixCollectionField($collection, $level)
    {
        foreach ($collection as $key => $value) {
            if ($this->isEntityDetached($value)) {
                $value = $this->reloadEntity($value);
                $collection->set($key, $value);
            } else {
                $this->fixEntityAssociationFields($value, $level - 1);
            }
        }
    }

    /**
     * @param mixed $entity
     * @param string $fieldName
     * @param mixed $value
     * @param int $level
     */
    protected function fixEntityField($entity, $fieldName, $value, $level)
    {
        if ($this->isEntityDetached($value)) {
            $value = $this->reloadEntity($value);
            PropertyAccess::createPropertyAccessor()->setValue($entity, $fieldName, $value);
        } else {
            $this->fixEntityAssociationFields($value, $level - 1);
        }
    }

    /**
     * @param object $entity
     * @return object
     */
    protected function reloadEntity($entity)
    {
        $entityClass = get_class($entity);
        $id = $this->entityManager->getClassMetadata($entityClass)->getIdentifierValues($entity);
        return $this->entityManager->find($entityClass, $id);
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isEntityDetached($entity)
    {
        return $this->entityManager->getUnitOfWork()->getEntityState($entity) == UnitOfWork::STATE_DETACHED;
    }
}
