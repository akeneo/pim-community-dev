<?php

namespace Oro\Bundle\SecurityBundle\Owner;

use Oro\Bundle\EntityBundle\ORM\EntityClassAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

/**
 * This class allows to get the owner of an entity
 */
class EntityOwnerAccessor
{
    /**
     * @var EntityClassAccessor
     */
    protected $entityClassAccessor;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $metadataProvider;

    /**
     * Constructor
     *
     * @param EntityClassAccessor $entityClassAccessor
     * @param OwnershipMetadataProvider $metadataProvider
     */
    public function __construct(
        EntityClassAccessor $entityClassAccessor,
        OwnershipMetadataProvider $metadataProvider
    ) {
        $this->entityClassAccessor = $entityClassAccessor;
        $this->metadataProvider = $metadataProvider;
    }

    /**
     * Gets owner of the given entity
     *
     * @param object $object
     * @return object
     * @throws \RuntimeException
     */
    public function getOwner($object)
    {
        if (!is_object($object)) {
            throw new InvalidEntityException('$object must be an object.');
        }

        $result = null;
        $metadata = $this->metadataProvider->getMetadata($this->entityClassAccessor->getClass($object));
        if ($metadata->hasOwner()) {
            // at first try to use getOwner method to get the owner
            if (method_exists($object, 'getOwner')) {
                $result = $object->getOwner();
            } else {
                // if getOwner method does not exist try to get owner directly from field
                try {
                    $cls = new \ReflectionClass($object);
                    $ownerProp = $cls->getProperty($metadata->getOwnerFieldName());
                    if (!$ownerProp->isPublic()) {
                        $ownerProp->setAccessible(true);
                    }
                    $result = $ownerProp->getValue($object);
                } catch (\ReflectionException $ex) {
                    throw new InvalidEntityException(
                        sprintf(
                            '$object must have either "getOwner" method or "%s" property.',
                            $metadata->getOwnerFieldName()
                        ),
                        0,
                        $ex
                    );
                }
            }
        }

        return $result;
    }
}
