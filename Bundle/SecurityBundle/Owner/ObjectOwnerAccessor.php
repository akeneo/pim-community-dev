<?php

namespace Oro\Bundle\SecurityBundle\Owner;

use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;

/**
 * This class allows to get the owner of a domain object
 */
class ObjectOwnerAccessor
{
    /**
     * @var ObjectClassAccessor
     */
    protected $objectClassAccessor;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $metadataProvider;

    /**
     * Constructor
     *
     * @param ObjectClassAccessor $objectClassAccessor
     * @param OwnershipMetadataProvider $metadataProvider
     */
    public function __construct(
        ObjectClassAccessor $objectClassAccessor,
        OwnershipMetadataProvider $metadataProvider
    ) {
        $this->objectClassAccessor = $objectClassAccessor;
        $this->metadataProvider = $metadataProvider;
    }

    /**
     * Gets owner of the given domain object
     *
     * @param object $domainObject
     * @return object
     * @throws InvalidDomainObjectException
     */
    public function getOwner($domainObject)
    {
        if (!is_object($domainObject)) {
            throw new InvalidDomainObjectException('$domainObject must be an object.');
        }

        $result = null;
        $metadata = $this->metadataProvider->getMetadata($this->objectClassAccessor->getClass($domainObject));
        if ($metadata->hasOwner()) {
            // at first try to use getOwner method to get the owner
            if (method_exists($domainObject, 'getOwner')) {
                $result = $domainObject->getOwner();
            } else {
                // if getOwner method does not exist try to get owner directly from field
                try {
                    $cls = new \ReflectionClass($domainObject);
                    $ownerProp = $cls->getProperty($metadata->getOwnerFieldName());
                    if (!$ownerProp->isPublic()) {
                        $ownerProp->setAccessible(true);
                    }
                    $result = $ownerProp->getValue($domainObject);
                } catch (\ReflectionException $ex) {
                    throw new InvalidDomainObjectException(
                        sprintf(
                            '$domainObject must have either "getOwner" method or "%s" property.',
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
