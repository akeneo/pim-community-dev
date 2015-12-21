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

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->managerRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function detach($object)
    {
        $objectManager = $this->getObjectManager($object);

        if ($objectManager instanceof DocumentManager) {
            $visited = [];
            $this->doDetach($object, $visited);
        } else {
            $objectManager->detach($object);
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
     * @param object $object
     * @param array  $visited Prevent infinite recursion
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
