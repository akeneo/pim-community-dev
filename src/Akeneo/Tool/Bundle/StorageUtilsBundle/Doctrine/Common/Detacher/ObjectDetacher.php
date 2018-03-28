<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
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
    /** @var ObjectManager */
    protected $objectManager;

    /** @var array */
    protected $scheduledForCheck;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->scheduledForCheck = null;
    }

    /**
     * {@inheritdoc}
     */
    public function detach($object)
    {
        $visited = [];
        $this->objectManager->detach($object);
        $this->doDetachScheduled($object, $visited);
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

        $objectManager = $this->objectManager;
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
        $class = $this->objectManager->getClassMetadata(ClassUtils::getClass($entity));

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

                    // no break
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
}
