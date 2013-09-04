<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as OID;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Oro\Bundle\SecurityBundle\Model\AclPermission;

class AclPrivilegeRepository
{
    const ROOT_PRIVILEGE_NAME = '(default)';

    /**
     * @var AclManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param AclManager $manager
     */
    final public function __construct(AclManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Gets a list of all permission names supported by ACL extension which is responsible
     * to process domain objects of the given type(s).
     * In case when $rootIdOrIds argument contains several identifiers this method returns
     * unique combination of all permission names supported by the requested ACL extensions.
     * For example if one ACL extension supports VIEW, CREATE and EDIT permissions
     * and another ACL extension supports VIEW and DELETE permissions,
     * the result will be: VIEW, CREATE, EDIT, DELETE
     *
     * @param string|string[] $rootIdOrIds The root identifier(s) returned by AclExtensionInterface::getRootId
     * @return string[]
     */
    public function getPermissionNames($rootIdOrIds)
    {
        if (is_string($rootIdOrIds)) {
            return $this->manager->getExtensionSelector()->select($this->manager->getRootOid($rootIdOrIds))
                ->getPermissions();
        }

        $result = array();
        foreach ($rootIdOrIds as $rootId) {
            $extension = $this->manager->getExtensionSelector()->select($this->manager->getRootOid($rootId));
            foreach ($extension->getPermissions() as $permission) {
                if (!in_array($permission, $result)) {
                    $result[] = $permission;
                }
            }
        }

        return $result;
    }

    /**
     * Gets all privileges associated with the given security identity.
     *
     * @param SID $sid
     * @return AclPrivilege[]
     */
    public function getPrivileges(SID $sid)
    {
        $privileges = new ArrayCollection();
        foreach ($this->manager->getAllExtensions() as $extension) {
            // fill a list of object identities;
            // the root object identity is added to the top of the list (for performance reasons)
            /** @var OID[] $oids */
            $oids = array();
            foreach ($extension->getClasses() as $class) {
                $oids[] = new OID($extension->getRootId(), $class);
            }
            $rootOid = $this->manager->getRootOid($extension->getRootId());
            array_unshift($oids, $rootOid);

            // load ACLs for all object identities
            $acls = $this->findAcls($sid, $oids);
            // find ACL for the root object identity
            $rootAcl = null;
            if ($acls->contains($rootOid)) {
                $rootAcl = $acls->offsetGet($rootOid);
            }

            foreach ($oids as $oid) {
                if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
                    $name = self::ROOT_PRIVILEGE_NAME;
                } else {
                    $name = '?';
                }
                $group = '';

                $privilege = new AclPrivilege();
                $privilege
                    ->setIdentity(
                        new AclPrivilegeIdentity(
                            $oid->getIdentifier() . ':' . $oid->getType(),
                            $name
                        )
                    )
                    ->setGroup($group)
                    ->setRootId($extension->getRootId());

                $this->addPermissions($privilege, $oid, $acls, $extension, $rootAcl);

                $privileges->add($privilege);
            }
        }

        $this->sortPrivileges($privileges);

        return $privileges;
    }

    /**
     * Associates privileges with the given security identity.
     *
     * @param SID $sid
     * @param array $privileges
     */
    public function savePrivileges(SID $sid, array $privileges)
    {

    }

    /**
     * Gets ACLs for given object identities
     *
     * @param SID $sid
     * @param OID[] $oids
     * @return \SplObjectStorage
     */
    protected function findAcls(SID $sid, array $oids)
    {
        try {
            return $this->manager->findAcls($sid, $oids);
        } catch (NotAllAclsFoundException $partial) {
            return $partial->getPartialResult();
        }
    }

    /**
     * Sorts the given privileges by name in alphabetical order.
     * The root privilege is moved at the top of the list.
     *
     * @param ArrayCollection|AclPrivilege[] $privileges
     */
    protected function sortPrivileges(ArrayCollection $privileges)
    {
        usort(
            $privileges,
            function (AclPrivilege $a, AclPrivilege $b) {
                return strpos($a->getIdentity()->getId(), ObjectIdentityFactory::ROOT_IDENTITY_TYPE)
                    ? 1
                    : strcmp($a->getIdentity()->getName(), $b->getIdentity()->getName());
            }
        );
    }

    /**
     * Adds permissions to the given $privilege.
     *
     * @param AclPrivilege $privilege
     * @param OID $oid
     * @param \SplObjectStorage $acls
     * @param AclExtensionInterface $extension
     * @param AclInterface $rootAcl
     */
    protected function addPermissions(
        AclPrivilege $privilege,
        OID $oid,
        \SplObjectStorage $acls,
        AclExtensionInterface $extension,
        AclInterface $rootAcl = null
    ) {
        $allowedPermissions = $extension->getAllowedPermissions($oid);
        if ($acls->contains($oid)) {
            $this->addAclPermissions(
                null,
                $privilege,
                $allowedPermissions,
                $extension,
                $acls->offsetGet($oid),
                $rootAcl
            );
        } elseif ($rootAcl !== null) {
            $this->addAclPermissions(null, $privilege, $allowedPermissions, $extension, $rootAcl);
        }

        foreach ($allowedPermissions as $permission) {
            if (!$privilege->hasPermission($permission)) {
                $privilege->addPermission(new AclPermission($permission, AccessLevel::NONE_LEVEL));
            }
        }
    }

    /**
     * Adds permissions to the given $privilege based on the given ACL.
     * The $permissions argument is used to filter privileges for the given permissions only.
     *
     * @param string|null $field The name of a field.
     *                           Set to null to work with class-based and object-based ACEs
     *                           Set to not null to work with class-field-based and object-field-based ACEs
     * @param AclPrivilege $privilege
     * @param string[] $permissions
     * @param AclExtensionInterface $extension
     * @param AclInterface $acl
     * @param AclInterface $rootAcl
     */
    protected function addAclPermissions(
        $field,
        AclPrivilege $privilege,
        array $permissions,
        AclExtensionInterface $extension,
        AclInterface $acl,
        AclInterface $rootAcl = null
    ) {
        // check object ACEs
        $this->addAcesPermissions(
            $privilege,
            $permissions,
            $this->manager->getAceProvider()->getAces($acl, AclManager::OBJECT_ACE, $field),
            $extension
        );
        // check class ACEs if object ACEs were not contains all requested privileges
        if ($privilege->getPermissionCount() < count($permissions)) {
            $this->addAcesPermissions(
                $privilege,
                $permissions,
                $this->manager->getAceProvider()->getAces($acl, AclManager::CLASS_ACE, $field),
                $extension
            );
        }
        // check parent ACEs if object and class ACEs were not contains all requested privileges
        if ($privilege->getPermissionCount() < count($permissions) && $acl->isEntriesInheriting()) {
            $parentAcl = $acl->getParentAcl();
            if ($parentAcl !== null) {
                $this->addAclPermissions($field, $privilege, $permissions, $extension, $parentAcl, $rootAcl);
            }
        }
        // if so far not all requested privileges are found get them from the root ACL
        if ($privilege->getPermissionCount() < count($permissions) && $rootAcl !== null) {
            $this->addAclPermissions($field, $privilege, $permissions, $extension, $rootAcl);
        }
    }

    /**
     * Adds permissions to the given $privilege based on the given ACEs.
     * The $permissions argument is used to filter privileges for the given permissions only.
     *
     * @param AclPrivilege $privilege
     * @param string[] $permissions
     * @param EntryInterface[] $aces
     * @param AclExtensionInterface $extension
     */
    protected function addAcesPermissions(
        AclPrivilege $privilege,
        array $permissions,
        array $aces,
        AclExtensionInterface $extension
    ) {
        if (!empty($aces)) {
            foreach ($aces as $ace) {
                if ($ace->isGranting()) {
                    // @todo denying ACE is not supported yet
                    continue;
                }
                $mask = $ace->getMask();
                foreach ($extension->getPermissions($mask) as $permission) {
                    if (isset($permissions[$permission]) && !$privilege->hasPermission($permission)) {
                        $privilege->addPermission(new AclPermission($permission, $extension->getAccessLevel($mask)));
                    }
                }
            }
        }
    }
}
