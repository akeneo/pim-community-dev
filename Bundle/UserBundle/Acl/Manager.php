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

class Manager implements ManagerInterface
{
    const ACL_ANNOTATION_CLASS = 'Oro\Bundle\UserBundle\Annotation\Acl';
    const ACL_ANCESTOR_ANNOTATION_CLASS = 'Oro\Bundle\UserBundle\Annotation\AclAncestor';

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var Reader
     */
    protected $aclReader;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var ConfigReader
     */
    protected $configReader;

    public function __construct(
        ObjectManager $em,
        Reader $aclReader,
        CacheProvider $cache,
        SecurityContextInterface $securityContext,
        ConfigReader $configReader
    ) {
        $this->em = $em;
        $this->aclReader = $aclReader;
        $this->cache = $cache;
        $this->securityContext = $securityContext;
        $this->configReader = $configReader;

        $this->cache->setNamespace('oro_user.cache');
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
     * @param  string $id
     *
     * @return Acl
     */
    public function getAclResource($id)
    {
        return $this->getAclRepo()->find($id);
    }

    /**
     * Get ACL Resources list
     *
     * @param  bool        $useObjects [optional] Use objects (true by default) or plain ids in response
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
     * @param  int  $aclResourceId ACL resource id
     * @param  User $user          [optional] User instance
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
     * @param  string $class
     * @param  string $method
     * @param  User   $user   [optional]
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

        $accessRoles = $acl
            ? $this->getRolesForAcl($acl)
            : $this->getAclRolesWithoutTree(Acl::ROOT_NODE);

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
     * @param  User        $user
     * @param  bool        $useObjects [optional]
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
     * @param  string $aclId
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
            foreach ($aclList as $aclId => $access) {
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
     * @param  Role  $role
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
     * @param  Role  $role
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

    /**
     * @return \Oro\Bundle\UserBundle\Entity\Repository\AclRepository
     */
    protected function getAclRepo()
    {
        return $this->em->getRepository('OroUserBundle:Acl');
    }

    /**
     * @param Acl  $resource
     * @param Role $role
     */
    protected function clearParentsAcl(Acl $resource, Role $role)
    {
        $resource->removeAccessRole($role);
        $this->em->persist($resource);

        if ($resource->getParent() && $resource->getParent()->getId() !== ACL::ROOT_NODE) {
            $this->clearParentsAcl($resource->getParent(), $role);
        }
    }

    /**
     * Create new db ACL Resources from array with ACL definition
     *
     * @param array $resources
     */
    protected function createNewResources(array $resources)
    {
        $resource = reset($resources);
        $bdResource = $this->createResource($resource);
        $resources = $this->setResourceParent($resources, $bdResource);

        unset($resources[$bdResource->getId()]);

        if (count($resources)) {
            $this->createNewResources($resources);
        }
    }

    /**
     * Set a parent for db ACL resource
     *
     * @param  array $resources
     * @param  Acl   $bdResource
     *
     * @return array
     */
    protected function setResourceParent(array $resources, Acl $bdResource)
    {
        $resource = $resources[$bdResource->getId()];

        if (!$resource->getParent()) {
            $parentResource = $this->getAclRepo()->find('root');
        } else {
            $parentResource = $this->getAclRepo()->find($resource->getParent());

            if (!$parentResource && isset($resources[$resource->getParent()])) {
                $parentResource = $this->createResource($resources[$resource->getParent()]);
                $resources = $this->setResourceParent($resources, $parentResource);

                unset($resources[$resource->getParent()]);
            }
        }

        $bdResource->setParent($parentResource);

        return $resources;
    }

    /**
     * Create new db ACL resource from annotation data
     *
     * @param  AnnotationAcl $resource
     *
     * @return Acl
     */
    protected function createResource(AnnotationAcl $resource)
    {
        $dbResource = new Acl();

        $dbResource->setId($resource->getId());
        $dbResource->setData($resource);

        $this->em->persist($dbResource);

        return $dbResource;
    }

    /**
     * @param  Acl   $acl [optional]
     *
     * @return array
     */
    protected function getRolesForAcl(Acl $acl = null)
    {
        $accessRoles = array();

        if ($acl) {
            $aclNodes = $this->getAclRepo()->getFullNodeWithRoles($acl);

            foreach ($aclNodes as $node) {
                $roles = $node->getAccessRolesNames();
                $accessRoles = array_unique(array_merge($roles, $accessRoles));
            }
        }

        return $accessRoles;
    }

    /**
     * Get user roles
     * If user was not set in parameters, then user takes from Security Context.
     * If user was not logged and was not set in parameters, then return IS_AUTHENTICATED_ANONYMOUSLY role
     *
     * @param  User  $user [optional]
     *
     * @return array
     */
    protected function getUserRoles(User $user = null)
    {
        if (null === $user) {
            $user = $this->getUser();
        }

        if ($user) {
            $roles = $this->cache->fetch('user-' . $user->getId());

            if (false === $roles) {
                $rolesObjects = $user->getRoles();

                foreach ($rolesObjects as $role) {
                    $roles[] = $role->getRole();
                }

                $this->cache->save('user-' . $user->getId(), $roles);
            }
        } else {
            $roles = array(User::ROLE_ANONYMOUS);
        }

        return $roles;
    }

    /**
     * Get a user from the Security Context
     *
     * @return mixed
     * @see    Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    protected function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * Check is grant access for roles to the acl resource roles
     *
     * @param  array $roles
     * @param  array $aclRoles
     *
     * @return bool
     */
    protected function checkIsGrant(array $roles, array $aclRoles)
    {
        foreach ($roles as $role) {
            if (in_array($role, $aclRoles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Acl Resources from annotations and configs
     *
     * @param string $directory
     *
     * @return \Oro\Bundle\UserBundle\Annotation\Acl[]
     */
    protected function getAclResourcesFromConfig($directory = '')
    {
        $resourcesFromAnnotations = $this->aclReader->getResources($directory);
        $resourcesFromConfigs = $this->configReader->getConfigResources($directory);

        return $resourcesFromAnnotations + $resourcesFromConfigs;
    }

    /**
     * Add ACL resource for Role
     *
     * @param  Acl  $acl
     * @param  Role $role
     *
     * @return True if ACL has been added, false otherwise
     */
    protected function addAclToRole(Acl $acl, Role $role)
    {
        if (!$acl->getAccessRoles()->contains($role)) {
            $role->addAclResource($acl);
            $acl->addAccessRole($role);

            if ($acl->getParent() && $acl->getParent()->getId() !== Acl::ROOT_NODE) {
                $this->clearParentsAcl($acl->getParent(), $role);
            }

            foreach ($role->getAclResources() as $resource) {
                if ($resource->getId() == Acl::ROOT_NODE) {
                    $resource->setAccessRoles(new ArrayCollection(array($role)));

                    break;
                }
            }

            $this->em->persist($acl);

            return true;
        }

        return false;
    }

    /**
     *
     * @param Acl  $acl
     * @param Role $role
     */
    protected function removeAclFromRole(Acl $acl, Role $role)
    {
        if ($acl->getAccessRoles()->contains($role)) {
            $acl->getAccessRoles()->removeElement($role);

            $this->em->persist($acl);

            return true;
        }

        return false;
    }
}
