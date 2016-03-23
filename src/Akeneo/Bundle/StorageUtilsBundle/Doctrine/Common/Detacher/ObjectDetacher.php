<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ORM\PersistentCollection as ORMPersistentCollection;
use Doctrine\ORM\UnitOfWork;

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
            $this->doDetach($object, $visited);
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
            function ($assoc) { return $assoc['isCascadeDetach']; }
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
     * @param document $document
     * @param array    $visited   Prevent infinite recursion
     */
    protected function doDetach($document, array &$visited)
    {
        $oid = spl_object_hash($document);
        if (isset($visited[$oid])) {
            return;
        }

        $documentManager = $this->getObjectManager($document);

        $visited[$oid] = $document;

        $documentManager->detach($document);

        $this->cascadeDetach($document, $visited);
    }

    /**
     * Cascade detach objects to overcome MongoDB detach
     * cascade bug on MongoDB ODM BETA12.
     * See https://github.com/doctrine/mongodb-odm/pull/979.
     *
     * @param mixed $object
     * @param array $visited Prevents infinite recursion
     */
    protected function cascadeDetach($document, array &$visited)
    {
        $documentManager = $this->getObjectManager($document);

        $class = $documentManager->getClassMetadata(ClassUtils::getClass($document));
        foreach ($class->fieldMappings as $mapping) {
            if (!$mapping['isCascadeDetach']) {
                continue;
            }
            $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
            if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                if ($relatedDocuments instanceof PersistentCollection) {
                    $relatedDocuments = $relatedDocuments->unwrap();
                }
                foreach ($relatedDocuments as $relatedDocument) {
                    $this->doDetach($relatedDocument, $visited);
                }
            } elseif ($relatedDocuments !== null) {
                $this->doDetach($relatedDocuments, $visited);
            }
        }
    }
}
