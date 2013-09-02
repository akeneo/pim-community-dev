<?php

namespace Oro\Bundle\SecurityBundle\Acl\Manager;

use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as OID;
use Symfony\Component\Security\Acl\Model\MutableAclInterface as ACL;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;
use Oro\Bundle\SecurityBundle\Acl\Manager\Batch\BatchItem;
use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AclManager
{
    const CLASS_ACE = 'Class';
    const OBJECT_ACE = 'Object';
    const CLASS_FIELD_ACE = 'ClassField';
    const OBJECT_FIELD_ACE = 'ObjectField';

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
     * This array contains all requested ACLs and flags indicate which changes are queued
     * key = a string unique for each OID
     * value = BatchItem
     *
     * @var BatchItem[]
     */
    protected $items = array();

    /**
     * Constructor
     *
     * @param ObjectIdentityFactory $objectIdentityFactory
     * @param AclExtensionSelector $extensionSelector
     * @param MutableAclProvider $aclProvider
     * @param AceManipulationHelper $aceProvider
     */
    public function __construct(
        ObjectIdentityFactory $objectIdentityFactory,
        AclExtensionSelector $extensionSelector,
        MutableAclProvider $aclProvider = null,
        AceManipulationHelper $aceProvider = null
    ) {
        $this->objectIdentityFactory = $objectIdentityFactory;
        $this->extensionSelector = $extensionSelector;
        $this->aclProvider = $aclProvider;
        $this->aceProvider = $aceProvider !== null
            ? $aceProvider
            : new AceManipulationHelper();
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
     * Flushes all changes to ACLs that have been queued up to now to the database.
     * This synchronizes the in-memory state of managed ACLs with the database.
     */
    public function flush()
    {
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
                $this->items = array();
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
     * Constructs SID (an object implements SecurityIdentityInterface) based on the given identity
     *
     * @param string|RoleInterface|UserInterface|TokenInterface $identity
     * @throws \InvalidArgumentException
     * @return SID
     */
    public function getSid($identity)
    {
        if (is_string($identity)) {
            return new RoleSecurityIdentity($identity);
        } elseif ($identity instanceof RoleInterface) {
            return new RoleSecurityIdentity($identity->getRole());
        } elseif ($identity instanceof UserInterface) {
            return UserSecurityIdentity::fromAccount($identity);
        } elseif ($identity instanceof TokenInterface) {
            return UserSecurityIdentity::fromToken($identity);
        }

        throw new \InvalidArgumentException(
            sprintf(
                '$identity must be a string or implement one of RoleInterface, UserInterface, TokenInterface'
                . ' (%s given)',
                is_object($identity) ? get_class($identity) : gettype($identity)
            )
        );
    }

    /**
     * Updates the security identity name.
     *
     * @param SID $sid An implementation of SecurityIdentityInterface created using the new name
     * @param string $oldName The old security identity name.
     *                        It is the user's username if $sid is UserSecurityIdentity
     *                        or the role name if $sid is RoleSecurityIdentity
     */
    public function updateSid(SID $sid, $oldName)
    {
        $this->aclProvider->updateSecurityIdentity($sid, $oldName);
    }

    /**
     * Deletes the given security identity.
     *
     * @param SID $sid
     */
    public function deleteSid(SID $sid)
    {
        $this->aclProvider->deleteSecurityIdentity($sid);
    }

    /**
     * Constructs an ObjectIdentity for the given domain object or based on the given descriptor
     *
     * Examples:
     *     getOid($object)
     *     getOid('Entity:AcmeBundle\SomeClass')
     *     getOid('Entity:AcmeBundle:SomeEntity')
     *     getOid('Action:Some Action')
     *
     * @param mixed $domainObjectOrDescriptor An domain object or the object identity descriptor
     * @return OID
     * @throws InvalidDomainObjectException
     */
    public function getOid($domainObjectOrDescriptor)
    {
        return $this->objectIdentityFactory->get($domainObjectOrDescriptor);
    }

    /**
     * Constructs an ObjectIdentity is used for grant default permissions
     * if more appropriate permissions are not specified
     *
     * @param string $rootId The root identifier returned by AclExtensionInterface::getRootId
     * @return OID
     */
    public function getRootOid($rootId)
    {
        return $this->objectIdentityFactory->root($rootId);
    }

    /**
     * Deletes an ACL for the given ObjectIdentity.
     *
     * @param OID $oid
     */
    public function deleteAcl(OID $oid)
    {
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
        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            $this->setObjectPermission($sid, $oid, $mask, $granting, $strategy);
        } else {
            $extension = $this->extensionSelector->select($oid);
            if ($oid->getType() === $extension->getRootId()) {
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
        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            throw new \InvalidArgumentException('Not supported for root ACL.');
        }

        $extension = $this->extensionSelector->select($oid);
        if ($oid->getType() === $extension->getRootId()) {
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
        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            $this->deleteObjectPermission($sid, $oid, $mask, $granting, $strategy);
        } else {
            $extension = $this->extensionSelector->select($oid);
            if ($oid->getType() === $extension->getRootId()) {
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
        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            throw new \InvalidArgumentException('Not supported for root ACL.');
        }

        $extension = $this->extensionSelector->select($oid);
        if ($oid->getType() === $extension->getRootId()) {
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
        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            $this->deleteAllObjectPermissions($sid, $oid);
        } else {
            $extension = $this->extensionSelector->select($oid);
            if ($oid->getType() === $extension->getRootId()) {
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
        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            throw new \InvalidArgumentException('Not supported for root ACL.');
        }

        $extension = $this->extensionSelector->select($oid);
        if ($oid->getType() === $extension->getRootId()) {
            $this->deleteAllClassFieldPermissions($sid, $oid, $field);
        } else {
            $this->deleteAllObjectFieldPermissions($sid, $oid, $field);
        }
    }

    /**
     * Gets all class-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @return EntryInterface[]
     */
    protected function getClassAces(OID $oid)
    {
        return $this->doGetAces($oid, self::CLASS_ACE, null);
    }

    /**
     * Gets all class-field-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @param string $field
     * @return EntryInterface[]
     */
    protected function getClassFieldAces(OID $oid, $field)
    {
        return $this->doGetAces($oid, self::CLASS_FIELD_ACE, $field);
    }

    /**
     * Gets all object-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @return EntryInterface[]
     */
    protected function getObjectAces(OID $oid)
    {
        return $this->doGetAces($oid, self::OBJECT_ACE, null);
    }

    /**
     * Gets all object-field-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @param string $field
     * @return EntryInterface[]
     */
    protected function getObjectFieldAces(OID $oid, $field)
    {
        return $this->doGetAces($oid, self::OBJECT_FIELD_ACE, $field);
    }

    /**
     * Gets all ACEs associated with given ACL
     *
     * @param OID $oid
     * @param string $type The ACE type. Can be one of self::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @return EntryInterface[]
     */
    protected function doGetAces(OID $oid, $type, $field)
    {
        $acl = $this->getAcl($oid);
        if (!$acl) {
            return array();
        }

        return $this->aceProvider->getAces($acl, $type, $field);
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
        $this->doSetPermission($sid, $oid, true, self::CLASS_FIELD_ACE, $field, $mask, $granting, $strategy);
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
        $this->doSetPermission($sid, $oid, true, self::OBJECT_FIELD_ACE, $field, $mask, $granting, $strategy);
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
            if ($acl === null && $this->items[$key]->getState() === BatchItem::STATE_CREATE) {
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
        $this->doDeletePermission($sid, $oid, self::CLASS_FIELD_ACE, $field, $mask, $granting, $strategy);
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
        $this->doDeletePermission($sid, $oid, self::OBJECT_FIELD_ACE, $field, $mask, $granting, $strategy);
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
        $this->doDeleteAllPermissions($sid, $oid, self::CLASS_FIELD_ACE, $field);
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
        $this->doDeleteAllPermissions($sid, $oid, self::OBJECT_FIELD_ACE, $field);
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
     * Gets an ACL for the given ObjectIdentity.
     * If an ACL does not exist $createAclIfNotExist sets to true a new ACL will be created.
     *
     * @param OID $oid
     * @param bool|null $ifNotExist Define what should be done if ACL does not exist. Defaults to null.
     *                              If null this method returns null if ACL does not exist.
     *                              If false this method throws AclNotFoundException if ACL does not exist.
     *                              If true  this method creates new ACL if ACL does not exist.
     * @return ACL
     * @throws AclNotFoundException
     */
    protected function getAcl(OID $oid, $ifNotExist = null)
    {
        $key = $this->getKey($oid);
        if (isset($this->items[$key])) {
            return $this->items[$key]->getAcl();
        }

        $acl = null;
        $state = BatchItem::STATE_NONE;
        try {
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
}
