<?php
namespace Oro\Bundle\UserBundle\Acl;

use Symfony\Component\Security\Core\SecurityContextInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Translation\MessageCatalogue;

use Oro\Bundle\UserBundle\Acl\ResourceReader\Reader;
use Oro\Bundle\UserBundle\Acl\ResourceReader\ConfigReader;
use Oro\Bundle\UserBundle\Acl\ManagerInterface;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Annotation\Acl as AnnotationAcl;

class Manager extends AclManipulator implements ManagerInterface
{
    const ACL_ANNOTATION_CLASS = 'Oro\Bundle\UserBundle\Annotation\Acl';
    const ACL_ANCESTOR_ANNOTATION_CLASS = 'Oro\Bundle\UserBundle\Annotation\AclAncestor';

    public function __construct(
        ObjectManager $em,
        Reader $aclReader,
        CacheProvider $cache,
        SecurityContextInterface $securityContext,
        ConfigReader $configReader
    ) {
        parent::__construct($em, $aclReader, $cache, $securityContext, $configReader);
    }

    /**
     * Add/Remove Acl resource for Role
     *
     * @param int    $roleId
     * @param string $aclId
     * @param bool   $isAdd
     */
    public function modifyAclForRole($roleId, $aclId, $isAdd = true)
    {
        /** @var $role \Oro\Bundle\UserBundle\Entity\Role */
        $role = $this->em->getRepository('OroUserBundle:Role')->find($roleId);

        /** @var $acl \Oro\Bundle\UserBundle\Entity\Acl */
        $acl = $this->getAclRepo()->find($aclId);

        if ($acl && $role) {
            $res = $isAdd
                ? $this->addAclToRole($acl, $role)
                : $this->removeAclFromRole($acl, $role);

            if (!$isAdd) {
                $aclIds = $this->getAclRepo()->getAllowedAclResourcesForRoles(array($role));
                $aclIds = array_diff($aclIds, array($aclId, 'root'));
                $aclIds = array_flip($aclIds);

                $this->saveRoleAcl($role, $aclIds);
            }

            if ($res) {
                $this->em->flush();
            }
        }
    }

    /**
     * Add/Remove Acl resources for Role
     *
     * @param int   $roleId Role id
     * @param array $aclIds ACL Resource IDs
     * @param bool  $isAdd  [optional] True if add, false if delete
     */
    public function modifyAclsForRole($roleId, array $aclIds, $isAdd = true)
    {
        foreach ($aclIds as $aclId) {
            $this->modifyAclForRole($roleId, $aclId, $isAdd);
        }
    }

    /**
     * Search Acl resource by id
     *
     * @param string $aclId
     *
     * @return Acl
     */
    public function getAclResource($aclId)
    {
        return $this->getAclRepo()->find($aclId);
    }

    /**
     * Get ACL Resources list
     *
     * @param bool $useObjects [optional] Use objects (true by default) or plain ids in response
     *
     * @return array|Acl[]
     */
    public function getAclResources($useObjects = true)
    {
        return $useObjects
            ? $this->getAclRepo()->findAll()
            : $this->getAclRepo()->getAclResourcesIds();
    }

    /**
     * Get list of allowed ACL resources for roles array
     *
     * @param Role[] $roles
     * @param bool   $useObjects [optional]
     *
     * @return array|Acl[]
     */
    public function getAllowedAclResourcesForRoles(array $roles, $useObjects = false)
    {
        return $this->getAclRepo()->getAllowedAclResourcesForRoles($roles, $useObjects);
    }

    /**
     * Check permissions for resource for user.
     *
     * @param int  $aclResourceId ACL resource id
     * @param User $user          [optional] User instance
     *
     * @return bool
     */
    public function isResourceGranted($aclResourceId, User $user = null)
    {
        return $this->checkIsGrant(
            $this->getUserRoles($user),
            $this->getAclRoles($aclResourceId)
        );
    }

    /**
     * @param string $class
     * @param string $method
     * @param User   $user   [optional]
     *
     * @return bool
     */
    public function isClassMethodGranted($class, $method, User $user = null)
    {
        $acl = $this->getAclRepo()->findOneBy(
            array(
                 'class'  => $class,
                 'method' => $method
            )
        );

        if (is_object($acl)) {
            $accessRoles = $this->getRolesForAcl($acl);
        } else {
            $accessRoles = $this->getAclRolesWithoutTree(Acl::ROOT_NODE);
        }

        return $this->checkIsGrant(
            $this->getUserRoles($user),
            $accessRoles
        );
    }

    /**
     * Get roles for acl id
     *
     * @param  $aclId
     *
     * @return array
     */
    public function getAclRolesWithoutTree($aclId)
    {
        $roles = $this->cache->fetch('acl-roles-' . $aclId);

        if (false === $roles) {
            $roles = $this->getAclRepo()->getAclRolesWithoutTree($aclId);

            $this->cache->save('acl-roles-' . $aclId, $roles);
        }

        return $roles;
    }

    /**
     * get array of resource ids allowed for user
     *
     * @param User $user
     * @param bool $useObjects [optional]
     *
     * @return array|Acl[]
     */
    public function getAclForUser(User $user, $useObjects = false)
    {
        if ($useObjects) {
            $acl = $this->getAclRepo()->getAllowedAclResourcesForRoles($user->getRoles(), true);
        } else {
            $cachePrefix = 'user-acl-' . $user->getId();
            $acl = $this->cache->fetch($cachePrefix);

            if (false === $acl) {
                $acl = $this->getAclRepo()->getAllowedAclResourcesForRoles($user->getRoles());

                $this->cache->save($cachePrefix, $acl);
            }
        }

        return $acl;
    }

    /**
     * Get roles for ACL resource from cache. If cache file does not exists - create new one.
     *
     * @param string $aclId
     *
     * @return array
     */
    public function getAclRoles($aclId)
    {
        $accessRoles = $this->cache->fetch($aclId);

        if (false === $accessRoles) {
            $accessRoles = $this->getRolesForAcl(
                $this->getAclRepo()->find($aclId)
            );

            $this->cache->save($aclId, $accessRoles);
        }

        return $accessRoles;
    }

    /**
     * Save roles for ACL Resource
     *
     * @param Role  $role
     * @param array $aclList [optional]
     */
    public function saveRoleAcl(Role $role, array $aclList = null)
    {
        $this->cache->deleteAll();

        $aclRepo = $this->getAclRepo();
        $aclCurrentList = $role->getAclResources();

        if ($aclCurrentList->count()) {
            foreach ($aclCurrentList as $acl) {
                $acl->removeAccessRole($role);

                $this->em->persist($acl);
            }
        }

        if (is_array($aclList) && count($aclList)) {
            $aclKeys = array_keys($aclList);
            foreach ($aclKeys as $aclId) {
                /** @var $resource \Oro\Bundle\UserBundle\Entity\Acl */
                $resource = $aclRepo->find($aclId);

                $resource->addAccessRole($role);

                if ($resource->getParent() && $resource->getParent()->getId() !== 'root') {
                    $this->clearParentsAcl($resource->getParent(), $role);
                }

                $this->em->persist($resource);
            }
        }

        $this->em->flush();
    }

    /**
     * Get Acl tree for role
     *
     * @param Role $role
     *
     * @return array
     */
    public function getRoleAclTree(Role $role)
    {
        return $this->getAclRepo()->getRoleAclTree($role);
    }

    /**
     * Get Acl list for role
     *
     * @param Role $role
     *
     * @return array
     */
    public function getRoleAcl(Role $role)
    {
        return $this->getAclRepo()->getAclListWithRoles($role);
    }

    /**
     * Get array with ACL translation tokens of bundle
     *
     * @param string $bundlePath
     *
     * @return array
     */
    public function getBundleAclTexts($bundlePath)
    {
        $messages = array();
        $resources = $this->getAclResourcesFromConfig($bundlePath);
        foreach ($resources as $resource) {
            /** @var $resource \Oro\Bundle\UserBundle\Annotation\Acl */
            $messages[] = $resource->getName();
            $messages[] = $resource->getDescription();
        }

        return array_unique($messages);
    }

    /**
     * Parse ACL translation tokens to the catalog
     *
     * @param string           $bundlePath
     * @param MessageCatalogue $catalog
     * @param string           $prefix
     */
    public function parseAclTokens($bundlePath, MessageCatalogue $catalog, $prefix = '')
    {
        $messages = $this->getBundleAclTexts($bundlePath);
        foreach ($messages as $message) {
            $catalog->set($message, $prefix . $message);
        }
    }

    /**
     * Synchronize acl resources from db with resources from annotations
     */
    public function synchronizeAclResources()
    {
        $resources = $this->getAclResourcesFromConfig();
        $bdResources = $this->getAclRepo()->findAll();

        // update old resources
        foreach ($bdResources as $num => $bdResource) {
            /** @var \Oro\Bundle\UserBundle\Entity\Acl $bdResource */
            if (isset($resources[$bdResource->getId()])) {
                $resource = $resources[$bdResource->getId()];
                $bdResource->setData($resource);
                $resources = $this->setResourceParent($resources, $bdResource);

                $this->em->persist($bdResource);

                unset($bdResources[$num]);
                unset($resources[$bdResource->getId()]);
            }
        }

        // delete resources
        if (count($bdResources)) {
            foreach ($bdResources as $bdResource) {
                if ($bdResource->getId() != 'root') {
                    $this->em->remove($bdResource);
                }
            }
        }

        // add new resources
        if (count($resources)) {
            $this->createNewResources($resources);
        }

        $this->em->flush();
    }
}
