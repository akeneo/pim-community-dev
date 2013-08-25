<?php

namespace Oro\Bundle\SecurityBundle\Owner;

use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipDecisionMakerInterface;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;

/**
 * This class implements OwnershipDecisionMakerInterface interface and allows to make ownership related
 * decisions using the tree of owners.
 */
class OwnershipDecisionMaker implements OwnershipDecisionMakerInterface
{
    /**
     * @var OwnerTree
     */
    protected $tree;

    /**
     * @var ObjectClassAccessor
     */
    protected $objectClassAccessor;

    /**
     * @var ObjectIdAccessor
     */
    protected $objectIdAccessor;

    /**
     * @var ObjectOwnerAccessor
     */
    protected $objectOwnerAccessor;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $metadataProvider;

    /**
     * Constructor
     *
     * @param OwnerTree $ownerTree
     * @param ObjectClassAccessor $objectClassAccessor
     * @param ObjectIdAccessor $objectIdAccessor
     * @param ObjectOwnerAccessor $objectOwnerAccessor
     * @param OwnershipMetadataProvider $metadataProvider
     */
    public function __construct(
        OwnerTree $ownerTree,
        ObjectClassAccessor $objectClassAccessor,
        ObjectIdAccessor $objectIdAccessor,
        ObjectOwnerAccessor $objectOwnerAccessor,
        OwnershipMetadataProvider $metadataProvider
    ) {
        $this->tree = $ownerTree;
        $this->objectClassAccessor = $objectClassAccessor;
        $this->objectIdAccessor = $objectIdAccessor;
        $this->objectOwnerAccessor = $objectOwnerAccessor;
        $this->metadataProvider = $metadataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isOrganization($domainObject)
    {
        return is_a($domainObject, $this->metadataProvider->getOrganizationClass());
    }

    /**
     * {@inheritdoc}
     */
    public function isBusinessUnit($domainObject)
    {
        return is_a($domainObject, $this->metadataProvider->getBusinessUnitClass());
    }

    /**
     * {@inheritdoc}
     */
    public function isUser($domainObject)
    {
        return is_a($domainObject, $this->metadataProvider->getUserClass());
    }

    /**
     * {@inheritdoc}
     */
    public function isBelongToOrganization($user, $domainObject)
    {
        $this->validateUserObject($user);
        $this->validateObject($domainObject);

        if ($this->isOrganization($domainObject)) {
            $userOrganizationId = $this->tree->getUserOrganizationId($this->getObjectId($user));
            if ($userOrganizationId === null) {
                return false;
            }

            return $this->getObjectId($domainObject) === $userOrganizationId;
        }

        if ($this->isBusinessUnit($domainObject)) {
            $userOrganizationId = $this->tree->getUserOrganizationId($this->getObjectId($user));
            if ($userOrganizationId === null) {
                return false;
            }
            $organizationId = $this->tree->getBusinessUnitOrganizationId($this->getObjectId($domainObject));

            return $organizationId === $userOrganizationId;
        }

        $metadata = $this->getObjectMetadata($domainObject);
        if (!$metadata->hasOwner()) {
            return false;
        }

        $userOrganizationId = $this->tree->getUserOrganizationId($this->getObjectId($user));
        if ($userOrganizationId === null) {
            return false;
        }

        $ownerId = $this->getObjectIdIgnoreNull($this->getOwner($domainObject));
        if ($metadata->isOrganizationOwned()) {
            return $userOrganizationId === $ownerId;
        } elseif ($metadata->isBusinessUnitOwned()) {
            return $userOrganizationId === $this->tree->getBusinessUnitOrganizationId($ownerId);
        } elseif ($metadata->isUserOwned()) {
            return $userOrganizationId === $this->tree->getUserOrganizationId($ownerId);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isBelongToBusinessUnit($user, $domainObject, $deep = false)
    {
        $this->validateUserObject($user);
        $this->validateObject($domainObject);

        if ($this->isBusinessUnit($domainObject)) {
            return $this->isUserBusinessUnit($this->getObjectId($user), $this->getObjectId($domainObject), $deep);
        }

        $metadata = $this->getObjectMetadata($domainObject);
        if (!$metadata->hasOwner()) {
            return false;
        }

        $ownerId = $this->getObjectIdIgnoreNull($this->getOwner($domainObject));
        if ($metadata->isBusinessUnitOwned()) {
            return $this->isUserBusinessUnit($this->getObjectId($user), $ownerId, $deep);
        } elseif ($metadata->isUserOwned()) {
            $businessUnitId = $this->tree->getUserBusinessUnitId($ownerId);
            if ($businessUnitId === null) {
                return false;
            }

            return $this->isUserBusinessUnit(
                $this->getObjectId($user),
                $this->tree->getUserBusinessUnitId($ownerId),
                $deep
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isBelongToUser($user, $domainObject)
    {
        $this->validateUserObject($user);
        $this->validateObject($domainObject);

        if ($this->isUser($domainObject)) {
            return $this->getObjectId($domainObject) === $this->getObjectId($user);
        }

        $metadata = $this->getObjectMetadata($domainObject);
        if ($metadata->isUserOwned()) {
            $ownerId = $this->getObjectIdIgnoreNull($this->getOwner($domainObject));

            return $this->getObjectId($user) === $ownerId;
        }

        return false;
    }

    /**
     * Determines whether the given user has a relation to the given business unit
     *
     * @param int|string $userId
     * @param int|string|null $businessUnitId
     * @param bool $deep Specify whether subordinate business units should be checked. Defaults to false.
     * @return bool
     */
    protected function isUserBusinessUnit($userId, $businessUnitId, $deep = false)
    {
        if ($businessUnitId === null) {
            return false;
        }

        $userOwningBusinessUnitId = $this->tree->getUserBusinessUnitId($userId);
        if ($userOwningBusinessUnitId !== null) {
            if ($businessUnitId === $userOwningBusinessUnitId) {
                return true;
            }
            if ($deep && $this->isSubordinateBusinessUnit($businessUnitId, $userOwningBusinessUnitId)) {
                return true;
            }
        }

        foreach ($this->tree->getUserBusinessUnitIds($userId) as $buId) {
            $alreadyTested = $userOwningBusinessUnitId !== null && $buId === $userOwningBusinessUnitId;
            if (!$alreadyTested) {
                if ($businessUnitId === $buId) {
                    return true;
                }
                if ($deep && $this->isSubordinateBusinessUnit($businessUnitId, $buId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determines whether the given business unit is subordinate one for the given parent business unit
     *
     * @param int|string $businessUnitIdToTest A business unit id to be tested if it is subordinate business unit
     * @param int|string $parentBusinessUnitId The parent business unit id
     * @return bool
     */
    protected function isSubordinateBusinessUnit($businessUnitIdToTest, $parentBusinessUnitId)
    {
        foreach ($this->tree->getSubordinateBusinessUnitIds($parentBusinessUnitId) as $buId) {
            if ($businessUnitIdToTest === $buId) {
                return true;
            };
        }

        return false;
    }

    /**
     * Check that the given object is a user
     *
     * @param object $user
     * @throws InvalidDomainObjectException
     */
    protected function validateUserObject($user)
    {
        if (!is_object($user) || !$this->isUser($user)) {
            throw new InvalidDomainObjectException(
                sprintf(
                    '$user must be an instance of %s.',
                    $this->metadataProvider->getUserClass()
                )
            );
        }
    }

    /**
     * Check that the given object is a domain object
     *
     * @param object $domainObject
     * @throws InvalidDomainObjectException
     */
    protected function validateObject($domainObject)
    {
        if (!is_object($domainObject)) {
            throw new InvalidDomainObjectException('$domainObject must be an object.');
        }
    }

    /**
     * Gets id for the given domain object
     *
     * @param object $domainObject
     * @return int|string
     * @throws InvalidDomainObjectException
     */
    protected function getObjectId($domainObject)
    {
        return $this->objectIdAccessor->getId($domainObject);
    }

    /**
     * Gets id for the given domain object.
     * Returns null when the given domain object is null
     *
     * @param object|null $domainObject
     * @return int|string|null
     * @throws InvalidDomainObjectException
     */
    protected function getObjectIdIgnoreNull($domainObject)
    {
        if ($domainObject === null) {
            return null;
        }

        return $this->objectIdAccessor->getId($domainObject);
    }

    /**
     * Gets the real class name for the given domain object or the given class name that could be a proxy
     *
     * @param object|string $domainObjectOrClassName
     * @return string
     */
    protected function getObjectClass($domainObjectOrClassName)
    {
        return $this->objectClassAccessor->getClass($domainObjectOrClassName);
    }

    /**
     * Gets metadata for the given domain object
     *
     * @param object $domainObject
     * @return OwnershipMetadata
     */
    protected function getObjectMetadata($domainObject)
    {
        return $this->metadataProvider->getMetadata($this->getObjectClass($domainObject));
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
        return $this->objectOwnerAccessor->getOwner($domainObject);
    }
}
