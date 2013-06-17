<?php

namespace Oro\Bundle\UserBundle\Acl;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;

use Oro\Bundle\UserBundle\Acl\ResourceReader\Reader;
use Oro\Bundle\UserBundle\Acl\ResourceReader\ConfigReader;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Annotation\Acl as AnnotationAcl;

abstract class AclManipulator
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var Reader
     */
    protected $aclReader;

    /**
     * @var ConfigReader
     */
    protected $configReader;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var CacheProvider
     */
    protected $cache;

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
     * Get Acl Resources from annotations and configs
     *
     * @param string $directory
     *
     * @return \Oro\Bundle\UserBundle\Annotation\Acl[]
     */
    protected function getAclResourcesFromConfig($directory = '')
    {
        $annotationRes = $this->aclReader->getResources($directory);
        $configRes = $this->configReader->getConfigResources($directory);

        return $annotationRes + $configRes;
    }

    /**
     * Add ACL resource for Role
     *
     * @param Acl  $acl
     * @param Role $role
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
     * @param Acl  $acl
     * @param Role $role
     *
     * @return bool
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
     * @param array $roles
     * @param array $aclRoles
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
     * Get user roles
     * If user was not set in parameters, then user takes from Security Context.
     * If user was not logged and was not set in parameters, then return IS_AUTHENTICATED_ANONYMOUSLY role
     *
     * @param User $user [optional]
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
     * Create new db ACL resource from annotation data
     *
     * @param AnnotationAcl $resource
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
     * @param Acl $acl [optional]
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
     * @param array $resources
     * @param Acl   $bdResource
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
     * @return \Oro\Bundle\UserBundle\Entity\Repository\AclRepository
     */
    protected function getAclRepo()
    {
        return $this->em->getRepository('OroUserBundle:Acl');
    }
}