<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\PersistentCollection as ORMPersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Detacher, detaches an object from its ObjectManager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectDetacher implements ObjectDetacherInterface, BulkObjectDetacherInterface
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var array */
    protected $scheduledForCheck;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->managerRegistry = $registry;
        $this->scheduledForCheck = null;
    }

    /**
     * {@inheritdoc}
     */
    public function detach($object)
    {
        $objectManager = $this->getObjectManager($object);
        $visited = [];

        if ($objectManager instanceof DocumentManager) {
            $this->doDetach($object);
            if ($object instanceof ProductInterface) {
                $this->hardcoreDetachForOdmUoW($object);
            }
        } else {
            $objectManager->detach($object);
            $this->doDetachScheduled($object, $visited);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detachAll(array $objects)
    {
        foreach ($objects as $object) {
            $this->detach($object);
        }
    }

    /**
     * Detach entity living in UoW's scheduledForDirtyCheck property.
     *
     * @param mixed $entity  The entity to be detached
     * @param array $visited Array of already detached entities
     */
    protected function doDetachScheduled($entity, array &$visited)
    {
        $oid = spl_object_hash($entity);
        if (isset($visited[$oid])) {
            return;
        }

        $objectManager = $this->getObjectManager($entity);
        $uow = $objectManager->getUnitOfWork();
        $class = $objectManager->getClassMetadata(ClassUtils::getClass($entity));
        $rootClassName = $class->rootEntityName;

        $visited[$oid] = $entity;

        if (null === $this->scheduledForCheck) {
            $this->scheduledForCheck = &$this->getScheduledForDirtyCheck($uow);
        }
        if (isset($this->scheduledForCheck[$rootClassName])) {
            unset($this->scheduledForCheck[$rootClassName][$oid]);
        }

        $this->cascadeDetachScheduled($entity, $visited);
    }

    /**
     * Cascades a detach entities associated to entities living in the
     * scheduledForDirtyCheck unit of work property.
     *
     * @param mixed $entity  The entity to be detached
     * @param array $visited Array of already detached entities
     */
    protected function cascadeDetachScheduled($entity, array &$visited)
    {
        $objectManager = $this->getObjectManager($entity);

        $class = $objectManager->getClassMetadata(ClassUtils::getClass($entity));

        $associationMappings = array_filter(
            $class->associationMappings,
            function ($assoc) {
                return $assoc['isCascadeDetach'];
            }
        );

        foreach ($associationMappings as $assoc) {
            $relatedEntities = $class->reflFields[$assoc['fieldName']]->getValue($entity);

            switch (true) {
                case ($relatedEntities instanceof ORMPersistentCollection):
                    // Unwrap for the foreach below
                    $relatedEntities = $relatedEntities->unwrap();

                case ($relatedEntities instanceof Collection):
                case (is_array($relatedEntities)):
                    foreach ($relatedEntities as $relatedEntity) {
                        $this->doDetachScheduled($relatedEntity, $visited);
                    }
                    break;

                case (null !== $relatedEntities):
                    $this->doDetachScheduled($relatedEntities, $visited);
                    break;
            }
        }
    }

    /**
     * ScheduledForDirtyCheck getter
     *
     * @param UnitOfWork $uow
     *
     * @return array
     */
    protected function &getScheduledForDirtyCheck(UnitOfWork $uow)
    {
        $closure = \Closure::bind(function &($uow) {
            return $uow->scheduledForDirtyCheck;
        }, null, $uow);

        return $closure($uow);
    }

    /**
     * @param object $object
     *
     * @return ObjectManager
     */
    protected function getObjectManager($object)
    {
        return $this->managerRegistry->getManagerForClass(ClassUtils::getClass($object));
    }

    /**
     * Do detach objects on DocumentManager
     *
     * @param mixed $document
     */
    protected function doDetach($document)
    {
        if ($document instanceof ProductInterface) {
            foreach ($document->getValues() as $value) {
                if (null !== $value->getMedia()) {
                    $mediaManager = $this->getObjectManager($value->getMedia());
                    $mediaManager->detach($value->getMedia());
                }
            }
        }

        $documentManager = $this->getObjectManager($document);
        $documentManager->detach($document);
    }

    /**
     * There is bug in the Doctrine ODM detach method. Even if it's properly called,
     * some objects are still not detached and remain in the unit of work.
     *
     * This subtle and artistic piece of code aims to remove an object from the private
     * variables of the unit of work (originalDocumentData, parentAssociations, embeddedDocumentsRegistry and
     * identityMap).
     *
     * This is the worst piece of code I've coded in my whole life. And I know I loose a lot of karma with that...
     * But, for my defense, the idea of using closures to access and mutate private variables is not mine. It has
     * already been done in EE with the PublishProductMemoryCleaner.
     *
     * @param mixed $object
     */
    private function hardcoreDetachForOdmUoW($object)
    {
        $objectManager = $this->getObjectManager($object);
        $uow = $objectManager->getUnitOfWork();
        $objectIds = [spl_object_hash($object)];

        $originalDocumentData = &$this->getOriginalDocumentData($uow);
        foreach (array_diff(array_keys($originalDocumentData), $objectIds) as $id) {
            unset($originalDocumentData[$id]);
        }

        $parentAssociations = &$this->getParentAssociations($uow);
        foreach (array_diff(array_keys($parentAssociations), $objectIds) as $id) {
            unset($parentAssociations[$id]);
        }

        $embeddedDocumentsRegistry = &$this->getEmbeddedDocumentsRegistry($uow);
        foreach (array_diff(array_keys($embeddedDocumentsRegistry), $objectIds) as $id) {
            unset($embeddedDocumentsRegistry[$id]);
        }

        $identityMap = &$this->getIdentityMap($uow);
        foreach (array_diff(array_keys($identityMap), $objectIds) as $id) {
            unset($identityMap[$id]);
        }
    }

    /**
     * Get the private originalDocumentData from UoW
     *
     * @param UnitOfWork $uow
     *
     * @return array
     */
    private function &getOriginalDocumentData($uow)
    {
        $closure = \Closure::bind(function &($uow) {
            return $uow->originalDocumentData;
        }, null, $uow);

        return $closure($uow);
    }

    /**
     * Get the private parentAssociations from UoW
     *
     * @param UnitOfWork $uow
     *
     * @return array
     */
    private function &getParentAssociations($uow)
    {
        $closure = \Closure::bind(function &($uow) {
            return $uow->parentAssociations;
        }, null, $uow);

        return $closure($uow);
    }

    /**
     * Get the private parentAssociations from UoW
     *
     * @param UnitOfWork $uow
     *
     * @return array
     */
    private function &getEmbeddedDocumentsRegistry($uow)
    {
        $closure = \Closure::bind(function &($uow) {
            return $uow->embeddedDocumentsRegistry;
        }, null, $uow);

        return $closure($uow);
    }

    /**
     * Get the private identityMap from UoW
     *
     * @param UnitOfWork $uow
     *
     * @return array
     */
    private function &getIdentityMap($uow)
    {
        $closure = \Closure::bind(function &($uow) {
            return $uow->identityMap;
        }, null, $uow);

        return $closure($uow);
    }
}
