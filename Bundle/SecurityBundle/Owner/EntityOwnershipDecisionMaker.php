<?php

namespace Oro\Bundle\SecurityBundle\Owner;

use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipDecisionMakerInterface;
use Oro\Bundle\EntityBundle\ORM\EntityClassAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;

/**
 * This class implements OwnershipDecisionMakerInterface interface and allows to make ownership related
 * decisions using the tree of owners.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class EntityOwnershipDecisionMaker implements OwnershipDecisionMakerInterface
{
    /**
     * @var OwnerTreeProvider
     */
    protected $treeProvider;

    /**
     * @var EntityClassAccessor
     */
    protected $entityClassAccessor;

    /**
     * @var ObjectIdAccessor
     */
    protected $objectIdAccessor;

    /**
     * @var EntityOwnerAccessor
     */
    protected $entityOwnerAccessor;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $metadataProvider;

    /**
     * Constructor
     *
     * @param OwnerTreeProvider         $treeProvider
     * @param EntityClassAccessor       $entityClassAccessor
     * @param ObjectIdAccessor          $objectIdAccessor
     * @param EntityOwnerAccessor       $entityOwnerAccessor
     * @param OwnershipMetadataProvider $metadataProvider
     */
    public function __construct(
        OwnerTreeProvider $treeProvider,
        EntityClassAccessor $entityClassAccessor,
        ObjectIdAccessor $objectIdAccessor,
        EntityOwnerAccessor $entityOwnerAccessor,
        OwnershipMetadataProvider $metadataProvider
    ) {
        $this->treeProvider = $treeProvider;
        $this->entityClassAccessor = $entityClassAccessor;
        $this->objectIdAccessor = $objectIdAccessor;
        $this->entityOwnerAccessor = $entityOwnerAccessor;
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
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isAssociatedWithOrganization($user, $domainObject)
    {
        $tree = $this->treeProvider->getTree();
        $this->validateUserObject($user);
        $this->validateObject($domainObject);

        if ($this->isOrganization($domainObject)) {
            $userOrganizationIds = $tree->getUserOrganizationIds($this->getObjectId($user));
            if (empty($userOrganizationIds)) {
                return false;
            }

            return in_array($this->getObjectId($domainObject), $userOrganizationIds);
        }

        if ($this->isBusinessUnit($domainObject)) {
            $userOrganizationIds = $tree->getUserOrganizationIds($this->getObjectId($user));
            if (empty($userOrganizationIds)) {
                return false;
            }

            return in_array(
                $tree->getBusinessUnitOrganizationId($this->getObjectId($domainObject)),
                $userOrganizationIds
            );
        }

        if ($this->isUser($domainObject)) {
            $userId = $this->getObjectId($user);
            $objId = $this->getObjectId($domainObject);
            if ($userId === $objId) {
                $userOrganizationId = $tree->getUserOrganizationId($userId);
                $objOrganizationId = $tree->getUserOrganizationId($objId);

                return $userOrganizationId !== null && $userOrganizationId === $objOrganizationId;
            }
        }

        $metadata = $this->getObjectMetadata($domainObject);
        if (!$metadata->hasOwner()) {
            return false;
        }

        $userOrganizationIds = $tree->getUserOrganizationIds($this->getObjectId($user));
        if (empty($userOrganizationIds)) {
            return false;
        }

        $ownerId = $this->getObjectIdIgnoreNull($this->getOwner($domainObject));
        if ($metadata->isOrganizationOwned()) {
            return in_array($ownerId, $userOrganizationIds);
        } elseif ($metadata->isBusinessUnitOwned()) {
            return in_array($tree->getBusinessUnitOrganizationId($ownerId), $userOrganizationIds);
        } elseif ($metadata->isUserOwned()) {
            return in_array($tree->getUserOrganizationId($ownerId), $userOrganizationIds);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAssociatedWithBusinessUnit($user, $domainObject, $deep = false)
    {
        $tree = $this->treeProvider->getTree();
        $this->validateUserObject($user);
        $this->validateObject($domainObject);

        if ($this->isBusinessUnit($domainObject)) {
            return $this->isUserBusinessUnit($this->getObjectId($user), $this->getObjectId($domainObject), $deep);
        }

        if ($this->isUser($domainObject)) {
            $userId = $this->getObjectId($user);
            if ($userId === $this->getObjectId($domainObject) && $tree->getUserBusinessUnitId($userId) !== null) {
                return true;
            }
        }

        $metadata = $this->getObjectMetadata($domainObject);
        if (!$metadata->hasOwner()) {
            return false;
        }

        $ownerId = $this->getObjectIdIgnoreNull($this->getOwner($domainObject));
        if ($metadata->isBusinessUnitOwned()) {
            return $this->isUserBusinessUnit($this->getObjectId($user), $ownerId, $deep);
        } elseif ($metadata->isUserOwned()) {
            $businessUnitId = $tree->getUserBusinessUnitId($ownerId);
            if ($businessUnitId === null) {
                return false;
            }

            return $this->isUserBusinessUnit(
                $this->getObjectId($user),
                $tree->getUserBusinessUnitId($ownerId),
                $deep
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAssociatedWithUser($user, $domainObject)
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
     * @param  int|string      $userId
     * @param  int|string|null $businessUnitId
     * @param  bool            $deep           Specify whether subordinate business units should be checked. Defaults to false.
     * @return bool
     */
    protected function isUserBusinessUnit($userId, $businessUnitId, $deep = false)
    {
        if ($businessUnitId === null) {
            return false;
        }

        foreach ($this->treeProvider->getTree()->getUserBusinessUnitIds($userId) as $buId) {
            if ($businessUnitId === $buId) {
                return true;
            }
            if ($deep && in_array($businessUnitId, $this->treeProvider->getTree()->getSubordinateBusinessUnitIds($buId))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check that the given object is a user
     *
     * @param  object                       $user
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
     * @param  object                       $domainObject
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
     * @param  object                       $domainObject
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
     * @param  object|null                  $domainObject
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
     * @param  object|string $domainObjectOrClassName
     * @return string
     */
    protected function getObjectClass($domainObjectOrClassName)
    {
        return $this->entityClassAccessor->getClass($domainObjectOrClassName);
    }

    /**
     * Gets metadata for the given domain object
     *
     * @param  object            $domainObject
     * @return OwnershipMetadata
     */
    protected function getObjectMetadata($domainObject)
    {
        return $this->metadataProvider->getMetadata($this->getObjectClass($domainObject));
    }

    /**
     * Gets owner of the given domain object
     *
     * @param  object                       $domainObject
     * @return object
     * @throws InvalidDomainObjectException
     */
    protected function getOwner($domainObject)
    {
        try {
            return $this->entityOwnerAccessor->getOwner($domainObject);
        } catch (InvalidEntityException $ex) {
            throw new InvalidDomainObjectException($ex->getMessage(), 0, $ex);
        }
    }
}
