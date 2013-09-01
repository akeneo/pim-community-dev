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
     */
    public function __construct(
        ObjectIdentityFactory $objectIdentityFactory,
        AclExtensionSelector $extensionSelector,
        MutableAclProvider $aclProvider = null
    ) {
        $this->objectIdentityFactory = $objectIdentityFactory;
        $this->extensionSelector = $extensionSelector;
        $this->aclProvider = $aclProvider;
        $this->aceProvider = new AceManipulationHelper();
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
     * Gets all class-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @return EntryInterface[]
     */
    public function getClassAces(OID $oid)
    {
        return $this->getAces($oid, self::CLASS_ACE, null);
    }

    /**
     * Gets all class-field-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @param string $field
     * @return EntryInterface[]
     */
    public function getClassFieldAces(OID $oid, $field)
    {
        return $this->getAces($oid, self::CLASS_FIELD_ACE, $field);
    }

    /**
     * Gets all object-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @return EntryInterface[]
     */
    public function getObjectAces(OID $oid)
    {
        return $this->getAces($oid, self::OBJECT_ACE, null);
    }

    /**
     * Gets all object-field-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @param string $field
     * @return EntryInterface[]
     */
    public function getObjectFieldAces(OID $oid, $field)
    {
        return $this->getAces($oid, self::OBJECT_FIELD_ACE, $field);
    }

    public function setClassPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->setPermission($sid, $oid, true, self::CLASS_ACE, null, $granting, $mask, $strategy);
    }

    public function setClassFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->setPermission($sid, $oid, true, self::CLASS_FIELD_ACE, $field, $granting, $mask, $strategy);
    }

    public function setObjectPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->setPermission($sid, $oid, true, self::OBJECT_ACE, null, $granting, $mask, $strategy);
    }

    public function setObjectFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->setPermission($sid, $oid, true, self::OBJECT_FIELD_ACE, $field, $granting, $mask, $strategy);
    }

    public function deleteClassPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->deletePermission($sid, $oid, self::CLASS_ACE, null, $granting, $mask, $strategy);
    }

    public function deleteClassFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->deletePermission($sid, $oid, self::CLASS_FIELD_ACE, $field, $granting, $mask, $strategy);
    }

    public function deleteObjectPermission(SID $sid, OID $oid, $mask, $granting = true, $strategy = null)
    {
        $this->deletePermission($sid, $oid, self::OBJECT_ACE, null, $granting, $mask, $strategy);
    }

    public function deleteObjectFieldPermission(SID $sid, OID $oid, $field, $mask, $granting = true, $strategy = null)
    {
        $this->deletePermission($sid, $oid, self::OBJECT_FIELD_ACE, $field, $granting, $mask, $strategy);
    }

    public function deleteAllClassPermissions(SID $sid, OID $oid)
    {
        $this->deleteAllPermissions($sid, $oid, self::CLASS_ACE, null);
    }

    public function deleteAllClassFieldPermissions(SID $sid, OID $oid, $field)
    {
        $this->deleteAllPermissions($sid, $oid, self::CLASS_FIELD_ACE, $field);
    }

    public function deleteAllObjectPermissions(SID $sid, OID $oid)
    {
        $this->deleteAllPermissions($sid, $oid, self::OBJECT_ACE, null);
    }

    public function deleteAllObjectFieldPermissions(SID $sid, OID $oid, $field)
    {
        $this->deleteAllPermissions($sid, $oid, self::OBJECT_FIELD_ACE, $field);
    }

    /**
     * Gets all class-based ACEs associated with given ACL
     *
     * @param OID $oid
     * @param string $type The ACE type. Can be one of self::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @return EntryInterface[]
     */
    protected function getAces(OID $oid, $type, $field)
    {
        $acl = $this->getAcl($oid);
        if (!$acl) {
            return array();
        }

        return $this->aceProvider->getAces($acl, $type, $field);
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
     * @param bool $granting
     * @param int $mask
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @throws InvalidAclMaskException
     */
    protected function setPermission(SID $sid, OID $oid, $replace, $type, $field, $granting, $mask, $strategy = null)
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
     * Deletes ACE with the given attributes
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $type The ACE type. Can be one of self::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param bool $granting
     * @param int $mask
     * @param string|null $strategy
     */
    protected function deletePermission(SID $sid, OID $oid, $type, $field, $granting, $mask, $strategy = null)
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
     * Deletes all ACEs the given type and security identity
     *
     * @param SID $sid
     * @param OID $oid
     * @param string $type The ACE type. Can be one of self::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     */
    protected function deleteAllPermissions(SID $sid, OID $oid, $type, $field)
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
