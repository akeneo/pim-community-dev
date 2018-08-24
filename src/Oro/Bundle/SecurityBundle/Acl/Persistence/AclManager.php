<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Persistence\Batch\BatchItem;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as OID;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface as ACL;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AclManager extends AbstractAclManager
{
    /**
     * We can not use BATCH_SIZE of Symfony ACL due to a bug check in the cache
     */
    const MAX_BATCH_SIZE = 1;

    const CLASS_ACE = 'Class';
    const OBJECT_ACE = 'Object';

    /**
     * @var ObjectIdentityFactory
     */
    protected $objectIdentityFactory;

    /**
     * @var AclExtensionSelector
     */
    protected $extensionSelector;

    /**
     * @var MutableAclProvider
     */
    private $aclProvider;

    /**
     * @var AceManipulationHelper
     */
    protected $aceProvider;

    /**
     * @var string
     */
    protected $privilegeRepositoryClass;

    /**
     * This array contains all requested ACLs and flags indicate which changes are queued
     * key = a string unique for each OID
     * value = BatchItem
     *
     * @var BatchItem[]
     */
    protected $items = [];

    /**
     * Constructor
     *
     * @param ObjectIdentityFactory $objectIdentityFactory
     * @param AclExtensionSelector $extensionSelector
     * @param MutableAclProvider $aclProvider
     * @param AceManipulationHelper $aceProvider
     * @param string|null $privilegeRepositoryClass
     */
    public function __construct(
        ObjectIdentityFactory $objectIdentityFactory,
        AclExtensionSelector $extensionSelector,
        MutableAclProvider $aclProvider = null,
        AceManipulationHelper $aceProvider = null,
        $privilegeRepositoryClass = null
    ) {
        $this->objectIdentityFactory = $objectIdentityFactory;
        $this->extensionSelector = $extensionSelector;
        $this->aclProvider = $aclProvider;
        $this->aceProvider = $aceProvider !== null
            ? $aceProvider
            : new AceManipulationHelper();
        $this->privilegeRepositoryClass = $privilegeRepositoryClass !== null
            ? $privilegeRepositoryClass
            : AclPrivilegeRepository::class;
    }

    /**
     * Indicates whether ACL based security is enabled or not
     *
     * @return bool
     */
    public function isAclEnabled()
    {
        return $this->aclProvider !== null;
    }

    /**
     * Gets ACL extension selector
     *
     * @return AclExtensionSelector
     */
    public function getExtensionSelector()
    {
        return $this->extensionSelector;
    }

    /**
     * Gets all ACL extension
     *
     * @return AclExtensionInterface[]
     */
    public function getAllExtensions()
    {
        return $this->extensionSelector->all();
    }

    /**
     * Gets the new instance of the mask builder which can be used to build permission bitmask
     * for an object with the given object identity.
     *
     * As one ACL extension can support several masks (each mask is stored in own ACE; an example of
     * ACL extension which supports several masks is 'Entity' extension - see EntityAclExtension class)
     * you need to provide any permission supported by expected mask builder instance.
     * Also you can omit $permission argument. In this case a default mask builder is returned.
     * For example the following calls return the same mask builder:
     *     $manager->getMaskBuilder($manager->getOid('entity: AcmeBundle:AcmeEntity'))
     *     $manager->getMaskBuilder($manager->getOid('entity: AcmeBundle:AcmeEntity'), 'VIEW')
     *     $manager->getMaskBuilder($manager->getOid('entity: AcmeBundle:AcmeEntity'), 'DELETE')
     * because VIEW, CREATE, EDIT, DELETE, ASSIGN and SHARE permissions are supported by EntityMaskBuilder class and
     * it is the default mask builder for 'Entity' extension.
     *
     * If you sure that some ACL extension supports only one mask, you can omit $permission argument as well.
     * For example the following calls are identical:
     *     $manager->getMaskBuilder($manager->getOid('action: Acme Action'))
     *     $manager->getMaskBuilder($manager->getOid('entity: Acme Action'), 'EXECUTE')
     *
     * @param OID $oid
     * @param string|null $permission Any permission you sure the expected mask builder supports
     * @return MaskBuilder
     */
    public function getMaskBuilder(OID $oid, $permission = null)
    {
        return $this->extensionSelector
            ->select($oid)
            ->getMaskBuilder($permission);
    }

    /**
     * Gets a repository for ACL privileges
     *
     * @return AclPrivilegeRepository
     */
    public function getPrivilegeRepository()
    {
        return new $this->privilegeRepositoryClass($this);
    }

    /**
     * Gets a provider responsible for manipulation of ACEs
     *
     * @return AceManipulationHelper
     */
    public function getAceProvider()
    {
        return $this->aceProvider;
    }

    /**
     * Flushes all changes to ACLs that have been queued up to now to the database.
     * This synchronizes the in-memory state of managed ACLs with the database.
     */
    public function flush()
    {
        $this->validateAclEnabled();

        $transactionStarted = false;
        try {
            foreach ($this->items as $item) {
                if ($item->getState() === BatchItem::STATE_NONE) {
                    continue;
                }
                if (!$transactionStarted) {
                    $this->aclProvider->beginTransaction();
                    $transactionStarted = true;
                }
                switch ($item->getState()) {
                    case BatchItem::STATE_CREATE:
                        $acl = $this->aclProvider->createAcl($item->getOid());
                        $hasChanges = false;
                        foreach ($item->getAces() as $ace) {
                            $hasChanges |= $this->aceProvider->setPermission(
                                $acl,
                                $this->extensionSelector->select($item->getOid()),
                                $ace->isReplace(),
                                $ace->getType(),
                                $ace->getField(),
                                $ace->getSecurityIdentity(),
                                $ace->isGranting(),
                                $ace->getMask(),
                                $ace->getStrategy()
                            );
                        }
                        if ($hasChanges) {
                            $this->aclProvider->updateAcl($acl);
                        }
                        break;
                    case BatchItem::STATE_UPDATE:
                        $this->aclProvider->updateAcl($item->getAcl());
                        break;
                    case BatchItem::STATE_DELETE:
                        $this->aclProvider->deleteAcl($item->getOid());
                        break;
                }
            }
            if ($transactionStarted) {
                $this->aclProvider->commit();
                $this->items = [];
            }
        } catch (\Exception $ex) {
            try {
                if ($transactionStarted) {
                    $this->aclProvider->rollBack();
                }
            } catch (\Exception $rollBackEx) {
                // ignore any exceptions during the rolling back operation
            }
            throw $ex;
        }
    }

    /**
     * Constructs an ObjectIdentity for the given domain object or based on the given descriptor
     *
     * The descriptor is a string in the following format: "ExtensionKey:Class"
     *
     * Examples:
     *     getOid($object)
     *     getOid('Entity:AcmeBundle\SomeClass')
     *     getOid('Entity:AcmeBundle:SomeEntity')
     *     getOid('Action:Some Action')
     *
     * @param mixed $val An domain object, object identity descriptor (id:type) or ACL annotation
     * @throws InvalidDomainObjectException
     * @return OID
     */
    public function getOid($val)
    {
        return $this->objectIdentityFactory->get($val);
    }

    /**
     * Constructs an ObjectIdentity is used for grant default permissions
     * if more appropriate permissions are not specified
     *
     * @param string $extensionKey The ACL extension key
     * @return OID
     */
    public function getRootOid($extensionKey)
    {
        return $this->objectIdentityFactory->root($extensionKey);
    }

    /**
     * Gets the ACLs that belong to the given object identities
     *
     * @param SID $sid
     * @param OID[] $oids
     * @throws NotAllAclsFoundException when we cannot find an ACL for all identities
     * @return \SplObjectStorage
     */
    public function findAcls(SID $sid, array $oids)
    {
        $this->validateAclEnabled();

        try {
            return $this->doFindAcls($oids, [$sid]);
        } catch (AclNotFoundException $ex) {
            if ($ex instanceof NotAllAclsFoundException) {
                $partialResultException = $ex;
            } else {
                $partialResultException = new NotAllAclsFoundException(
                    'The provider could not find ACLs for all object identities.'
                );
                $partialResultException->setPartialResult(new \SplObjectStorage());
            }
            throw $partialResultException;
        }
    }

    /**
     * Deletes an ACL for the given ObjectIdentity.
     *
     * @param OID $oid
     */
    public function deleteAcl(OID $oid)
    {
        $this->validateAclEnabled();

        $key = $this->getKey($oid);
        if (!isset($this->items[$key])) {
            $this->items[$key] = new BatchItem($oid, BatchItem::STATE_DELETE);
        } else {
            $this->items[$key]->setState(BatchItem::STATE_DELETE);
        }
    }

    /**
     * Updates or creates object-based or class-based ACE with the given attributes.
     *
     * If the given object identity represents a domain object the object-based ACE is set;
     * otherwise, class-based ACE is set.
     * If the given object identity represents a "root" ACL the object-based ACE is set.
     *
     * @param SID $sid
     * @param OID $oid
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @throws InvalidAclMaskException
     */
    public function setPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->validateAclEnabled();

        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            $this->setObjectPermission($sid, $oid, $mask, $granting, $strategy);
        } else {
            $extension = $this->extensionSelector->select($oid);
            if ($oid->getIdentifier() === $extension->getExtensionKey()) {
                $this->setClassPermission($sid, $oid, $mask, $granting, $strategy);
            } else {
                $this->setObjectPermission($sid, $oid, $mask, $granting, $strategy);
            }
        }
    }

    /**
     * Updates or creates object-field-based or class-field-based ACE with the given attributes.
     *
     * If the given object identity represents a domain object the object-field-based ACE is set;
     * otherwise, class-field-based ACE is set.
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @throws InvalidAclMaskException
     * @throws \InvalidArgumentException
     */
    public function setFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->validateAclEnabled();

        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            throw new \InvalidArgumentException('Not supported for root ACL.');
        }

        $extension = $this->extensionSelector->select($oid);
        if ($oid->getIdentifier() === $extension->getExtensionKey()) {
            $this->setClassFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
        } else {
            $this->setObjectFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
        }
    }

    /**
     * Deletes object-based or class-based ACE with the given attributes.
     *
     * If the given object identity represents a domain object the object-based ACE is deleted;
     * otherwise, class-based ACE is deleted.
     * If the given object identity represents a "root" ACL the object-based ACE is deleted.
     *
     * @param SID $sid
     * @param OID $oid
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null ACE with any strategy should be deleted
     * @throws InvalidAclMaskException
     */
    public function deletePermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->validateAclEnabled();

        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            $this->deleteObjectPermission($sid, $oid, $mask, $granting, $strategy);
        } else {
            $extension = $this->extensionSelector->select($oid);
            if ($oid->getIdentifier() === $extension->getExtensionKey()) {
                $this->deleteClassPermission($sid, $oid, $mask, $granting, $strategy);
            } else {
                $this->deleteObjectPermission($sid, $oid, $mask, $granting, $strategy);
            }
        }
    }

    /**
     * Deletes object-field-based or class-field-based ACE with the given attributes.
     *
     * If the given object identity represents a domain object the object-field-based ACE is deleted;
     * otherwise, class-field-based ACE is deleted.
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null ACE with any strategy should be deleted
     * @throws InvalidAclMaskException
     * @throws \InvalidArgumentException
     */
    public function deleteFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->validateAclEnabled();

        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            throw new \InvalidArgumentException('Not supported for root ACL.');
        }

        $extension = $this->extensionSelector->select($oid);
        if ($oid->getIdentifier() === $extension->getExtensionKey()) {
            $this->deleteClassFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
        } else {
            $this->deleteObjectFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
        }
    }

    /**
     * Deletes all object-based or class-based ACEs for the given security identity
     *
     * If the given object identity represents a domain object the object-based ACEs are deleted;
     * otherwise, class-based ACEs are deleted.
     * If the given object identity represents a "root" ACL the object-based ACEs are deleted.
     *
     * @param SID $sid
     * @param OID $oid
     * @throws InvalidAclMaskException
     */
    public function deleteAllPermissions(SID $sid, OID $oid)
    {
        $this->validateAclEnabled();

        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            $this->deleteAllObjectPermissions($sid, $oid);
        } else {
            $extension = $this->extensionSelector->select($oid);
            if ($oid->getIdentifier() === $extension->getExtensionKey()) {
                $this->deleteAllClassPermissions($sid, $oid);
            } else {
                $this->deleteAllObjectPermissions($sid, $oid);
            }
        }
    }

    /**
     * Deletes all object-field-based or class-field-based ACEs for the given security identity
     *
     * If the given object identity represents a domain object the object-field-based ACEs are deleted;
     * otherwise, class-field-based ACEs are deleted.
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @throws InvalidAclMaskException
     * @throws \InvalidArgumentException
     */
    public function deleteAllFieldPermissions(SID $sid, OID $oid, $field)
    {
        $this->validateAclEnabled();

        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            throw new \InvalidArgumentException('Not supported for root ACL.');
        }

        $extension = $this->extensionSelector->select($oid);
        if ($oid->getIdentifier() === $extension->getExtensionKey()) {
            $this->deleteAllClassFieldPermissions($sid, $oid, $field);
        } else {
            $this->deleteAllObjectFieldPermissions($sid, $oid, $field);
        }
    }

    /**
     * Gets all object-based or class-based ACEs associated with given ACL and the given security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @return EntryInterface[]
     */
    public function getAces(SID $sid, OID $oid)
    {
        $this->validateAclEnabled();

        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            return $this->doGetAces($sid, $oid, self::OBJECT_ACE, null);
        }
        $extension = $this->extensionSelector->select($oid);
        if ($oid->getIdentifier() === $extension->getExtensionKey()) {
            return $this->doGetAces($sid, $oid, self::CLASS_ACE, null);
        }

        return $this->doGetAces($sid, $oid, self::OBJECT_ACE, null);
    }

    /**
     * Gets all object-field-based or class-field-based ACEs associated with given ACL and the given security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @throws \InvalidArgumentException
     * @return EntryInterface[]
     */
    public function getFieldAces(SID $sid, OID $oid, $field)
    {
        $this->validateAclEnabled();

        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            throw new \InvalidArgumentException('Not supported for root ACL.');
        }

        $extension = $this->extensionSelector->select($oid);
        if ($oid->getIdentifier() === $extension->getExtensionKey()) {
            return $this->doGetAces($sid, $oid, self::CLASS_ACE, $field);
        }

        return $this->doGetAces($sid, $oid, self::OBJECT_ACE, $field);
    }

    /**
     * Clear ACLs provider cache
     */
    public function clearCache()
    {
        $this->aclProvider->clearCache();
    }

    /**
     * Updates or creates class-based ACE with the given attributes.
     *
     * @param SID $sid
     * @param OID $oid
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @throws InvalidAclMaskException
     */
    protected function setClassPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->doSetPermission($sid, $oid, true, self::CLASS_ACE, null, $mask, $granting, $strategy);
    }

    /**
     * Updates or creates object-based ACE with the given attributes.
     *
     * @param SID $sid
     * @param OID $oid
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @throws InvalidAclMaskException
     */
    protected function setObjectPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->doSetPermission($sid, $oid, true, self::OBJECT_ACE, null, $mask, $granting, $strategy);
    }

    /**
     * Updates or creates class-field-based ACE with the given attributes.
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @throws InvalidAclMaskException
     */
    protected function setClassFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->doSetPermission($sid, $oid, true, self::CLASS_ACE, $field, $mask, $granting, $strategy);
    }

    /**
     * Updates or creates object-field-based ACE with the given attributes.
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @throws InvalidAclMaskException
     */
    protected function setObjectFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->doSetPermission($sid, $oid, true, self::OBJECT_ACE, $field, $mask, $granting, $strategy);
    }

    /**
     * Updates or creates ACE with the given attributes
     *
     * @param SID $sid
     * @param OID $oid
     * @param bool $replace If true the mask and strategy of the existing ACE should be replaced with the given ones
     * @param string $type The ACE type. Can be one of self::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @throws InvalidAclMaskException
     */
    protected function doSetPermission(SID $sid, OID $oid, $replace, $type, $field, $mask, $granting, $strategy = null)
    {
        $acl = $this->getAcl($oid, true);
        $key = $this->getKey($oid);
        if ($this->items[$key]->getState() !== BatchItem::STATE_DELETE) {
            $extension = $this->extensionSelector->select($oid);
            $extension->validateMask($mask, $oid);
            if ((null === $acl || 0 === $acl->getId()) && $this->items[$key]->getState() === BatchItem::STATE_CREATE) {
                $this->items[$key]->addAce($type, $field, $sid, $granting, $mask, $strategy);
            } else {
                $hasChanges = $this->aceProvider->setPermission(
                    $acl,
                    $extension,
                    $replace,
                    $type,
                    $field,
                    $sid,
                    $granting,
                    $mask,
                    $strategy
                );
                if ($hasChanges) {
                    $this->items[$key]->setState(BatchItem::STATE_UPDATE);
                }
            }
        }
    }

    /**
     * Deletes class-based ACE with the given attributes.
     *
     * @param SID $sid
     * @param OID $oid
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null ACE with any strategy should be deleted
     * @throws InvalidAclMaskException
     */
    protected function deleteClassPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->doDeletePermission($sid, $oid, self::CLASS_ACE, null, $mask, $granting, $strategy);
    }

    /**
     * Deletes object-based ACE with the given attributes.
     *
     * @param SID $sid
     * @param OID $oid
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null ACE with any strategy should be deleted
     * @throws InvalidAclMaskException
     */
    protected function deleteObjectPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->doDeletePermission($sid, $oid, self::OBJECT_ACE, null, $mask, $granting, $strategy);
    }

    /**
     * Deletes class-field-based ACE with the given attributes.
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null ACE with any strategy should be deleted
     * @throws InvalidAclMaskException
     */
    protected function deleteClassFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->doDeletePermission($sid, $oid, self::CLASS_ACE, $field, $mask, $granting, $strategy);
    }

    /**
     * Deletes object-field-based ACE with the given attributes.
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null ACE with any strategy should be deleted
     * @throws InvalidAclMaskException
     */
    protected function deleteObjectFieldPermission(
        SID $sid,
        OID $oid,
        $field,
        $mask,
        $granting = true,
        $strategy = null
    ) {
        $this->doDeletePermission($sid, $oid, self::OBJECT_ACE, $field, $mask, $granting, $strategy);
    }

    /**
     * Deletes ACE with the given attributes
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $type The ACE type. Can be one of self::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param int $mask
     * @param bool $granting
     * @param string|null $strategy If null ACE with any strategy should be deleted
     */
    protected function doDeletePermission(SID $sid, OID $oid, $type, $field, $mask, $granting, $strategy = null)
    {
        $acl = $this->getAcl($oid);
        $key = $this->getKey($oid);
        if ($this->items[$key]->getState() !== BatchItem::STATE_DELETE) {
            if ($acl === null && $this->items[$key]->getState() === BatchItem::STATE_CREATE) {
                $this->items[$key]->removeAce($type, $field, $sid, $granting, $mask, $strategy);
            } else {
                $hasChanges = $this->aceProvider->deletePermission(
                    $acl,
                    $type,
                    $field,
                    $sid,
                    $granting,
                    $mask,
                    $strategy
                );
                if ($hasChanges) {
                    $this->items[$key]->setState(BatchItem::STATE_UPDATE);
                }
            }
        }
    }

    /**
     * Deletes all class-based ACEs for the given security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @throws InvalidAclMaskException
     */
    protected function deleteAllClassPermissions(SID $sid, OID $oid)
    {
        $this->doDeleteAllPermissions($sid, $oid, self::CLASS_ACE, null);
    }

    /**
     * Deletes all object-based ACEs for the given security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @throws InvalidAclMaskException
     */
    protected function deleteAllObjectPermissions(SID $sid, OID $oid)
    {
        $this->doDeleteAllPermissions($sid, $oid, self::OBJECT_ACE, null);
    }

    /**
     * Deletes all class-field-based ACEs for the given security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @throws InvalidAclMaskException
     */
    protected function deleteAllClassFieldPermissions(SID $sid, OID $oid, $field)
    {
        $this->doDeleteAllPermissions($sid, $oid, self::CLASS_ACE, $field);
    }

    /**
     * Deletes all object-field-based ACEs for the given security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $field
     * @throws InvalidAclMaskException
     */
    protected function deleteAllObjectFieldPermissions(SID $sid, OID $oid, $field)
    {
        $this->doDeleteAllPermissions($sid, $oid, self::OBJECT_ACE, $field);
    }

    /**
     * Deletes all ACEs the given type and security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $type The ACE type. Can be one of self::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     */
    protected function doDeleteAllPermissions(SID $sid, OID $oid, $type, $field)
    {
        $acl = $this->getAcl($oid);
        $key = $this->getKey($oid);
        if ($this->items[$key]->getState() !== BatchItem::STATE_DELETE) {
            if ($acl === null && $this->items[$key]->getState() === BatchItem::STATE_CREATE) {
                $this->items[$key]->removeAces($type, $field, $sid);
            } else {
                $hasChanges = $this->aceProvider->deleteAllPermissions($acl, $type, $field, $sid);
                if ($hasChanges) {
                    $this->items[$key]->setState(BatchItem::STATE_UPDATE);
                }
            }
        }
    }

    /**
     * Gets all ACEs associated with given ACL and the given security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $type The ACE type. Can be one of self::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @return EntryInterface[]
     */
    protected function doGetAces(SID $sid, OID $oid, $type, $field)
    {
        $acl = $this->getAcl($oid);
        if (!$acl) {
            return [];
        }

        return array_filter(
            $this->aceProvider->getAces($acl, $type, $field),
            function ($ace) use (&$sid) {
                /** @var EntryInterface $ace */

                return $sid->equals($ace->getSecurityIdentity());
            }
        );
    }

    /**
     * Gets the ACLs that belong to the given object identities
     *
     * We have to implement this method due a bug in AclProvider::findAcls:
     *     when $oids array length > AclProvider::MAX_BATCH_SIZE and no any ACLs were found for any bath, the findAcls
     *     method throws AclNotFoundException rather than continue loading ACLs for other batched and throw
     *     this exception only when no any ACLs found for all batches. But if at least one ACL (but not all) was found
     *     this method should throw NotAllAclsFoundException.
     *
     * @param OID[] $oids
     * @param SID[] $sids
     * @throws AclNotFoundException
     * @throws NotAllAclsFoundException
     * @return \SplObjectStorage mapping the passed object identities to ACLs
     */
    protected function doFindAcls(array $oids, array $sids)
    {
        // split object identities to batches (batch size must be less than or equal AclProvider::MAX_BATCH_SIZE)
        $oidsBatches = [];
        $batchIndex = 0;
        $oidsBatches[$batchIndex] = [];
        $index = 0;
        foreach ($oids as $oid) {
            /**
             * We can not use AclProvider::MAX_BATCH_SIZE of Symfony ACL due to a bug check in the cache
             */
            if ($index >= self::MAX_BATCH_SIZE) {
                $index = 0;
                $batchIndex++;
            }
            $oidsBatches[$batchIndex][] = $oid;
            $index++;
        }

        $result = null;
        foreach ($oidsBatches as $oidsBatch) {
            try {
                $acls = $this->aclProvider->findAcls($oidsBatch, $sids);
                if ($result === null) {
                    $result = $acls;
                } else {
                    foreach ($acls as $aclOid) {
                        $result->attach($aclOid, $acls->offsetGet($aclOid));
                    }
                }
            } catch (AclNotFoundException $ex) {
                if ($ex instanceof NotAllAclsFoundException) {
                    if ($result === null) {
                        $result = $ex->getPartialResult();
                    } else {
                        $partialResult = $ex->getPartialResult();
                        foreach ($partialResult as $aclOid) {
                            $result->attach($aclOid, $partialResult->offsetGet($aclOid));
                        }
                    }
                } else {
                    if ($result === null) {
                        $result = new \SplObjectStorage();
                    }
                }
            }
        }

        // check that we got ACLs for all the identities
        foreach ($oids as $oid) {
            if (!$result->contains($oid)) {
                if (1 === count($oids)) {
                    throw new AclNotFoundException(sprintf('No ACL found for %s.', $oid));
                }

                $partialResultEx = new NotAllAclsFoundException(
                    'The provider could not find ACLs for all object identities.'
                );
                $partialResultEx->setPartialResult($result);

                throw $partialResultEx;
            }
        }

        return $result;
    }

    /**
     * Gets an ACL for the given ObjectIdentity.
     * If an ACL does not exist $createAclIfNotExist sets to true a new ACL will be created.
     *
     * @param OID $oid
     * @param bool|null $ifNotExist Define what should be done if ACL does not exist. Defaults to null.
     *                              If null this method returns null if ACL does not exist.
     *                              If false this method throws AclNotFoundException if ACL does not exist.
     *                              If true  this method creates new ACL if ACL does not exist.
     * @throws AclNotFoundException
     * @return ACL
     */
    protected function getAcl(OID $oid, $ifNotExist = null)
    {
        $key = $this->getKey($oid);
        if (isset($this->items[$key])) {
            $item = $this->items[$key];
            // make sure that a new ACL has a correct state
            if (true === $ifNotExist && (null === $item->getAcl() || 0 === $item->getAcl()->getId())
                && $item->getState() === BatchItem::STATE_NONE) {
                $item->setState(BatchItem::STATE_CREATE);
            }

            return $item->getAcl();
        }

        $acl = null;
        $state = BatchItem::STATE_NONE;
        try {
            // We need clear ACL cache before finding ACL because it is possible that
            // non valid empty ACL is cached by MutableAclProvider::cacheEmptyAcl() method
            $this->aclProvider->clearOidCache($oid);
            $acl = $this->aclProvider->findAcl($oid);
        } catch (AclNotFoundException $ex) {
            if ($ifNotExist === true) {
                $state = BatchItem::STATE_CREATE;
            } elseif ($ifNotExist === false) {
                throw $ex;
            }
        }

        $this->items[$key] = new BatchItem($oid, $state, $acl);

        return $acl;
    }

    /**
     * Gets a key used to store ACL in $this->items collection
     *
     * @param OID $oid
     * @return string
     */
    protected function getKey(OID $oid)
    {
        return $oid->getType() . '!' . $oid->getIdentifier();
    }

    /**
     * Checks whether ACL is enabled and if not raise InvalidConfigurationException.
     *
     * @throws InvalidConfigurationException
     */
    protected function validateAclEnabled()
    {
        if ($this->aclProvider === null) {
            throw new InvalidConfigurationException(
                'Seems that ACL is not enabled. Please check "security/acl" parameter in "app/config/security.yml"'
            );
        }
    }
}
