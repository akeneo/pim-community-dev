<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Locale access manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class LocaleAccessManager
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $localeAccessClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $localeAccessClass
     */
    public function __construct(ManagerRegistry $registry, $localeAccessClass)
    {
        $this->registry            = $registry;
        $this->localeAccessClass = $localeAccessClass;
    }

    /**
     * Get roles that have view access to a locale
     *
     * @param Locale $locale
     *
     * @return Role[]
     */
    public function getViewRoles(Locale $locale)
    {
        return $this->getAccessRepository()->getGrantedRoles($locale, Attributes::VIEW_PRODUCTS);
    }

    /**
     * Get roles that have edit access to a locale
     *
     * @param Locale $locale
     *
     * @return Role[]
     */
    public function getEditRoles(Locale $locale)
    {
        return $this->getAccessRepository()->getGrantedRoles($locale, Attributes::EDIT_PRODUCTS);
    }

    /**
     * Grant access on an attribute locale to specified roles
     *
     * @param Locale $locale
     * @param Role[] $viewRoles
     * @param Role[] $editRoles
     */
    public function setAccess(Locale $locale, $viewRoles, $editRoles)
    {
        $grantedRoles = [];
        foreach ($editRoles as $role) {
            $this->grantAccess($locale, $role, Attributes::EDIT_PRODUCTS);
            $grantedRoles[] = $role;
        }

        foreach ($viewRoles as $role) {
            if (!in_array($role, $grantedRoles)) {
                $this->grantAccess($locale, $role, Attributes::VIEW_PRODUCTS);
                $grantedRoles[] = $role;
            }
        }

        $this->revokeAccess($locale, $grantedRoles);
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on an attribute locale for the provided role
     *
     * @param Locale $locale
     * @param Role   $role
     * @param string $accessLevel
     */
    public function grantAccess(Locale $locale, Role $role, $accessLevel)
    {
        $access = $this->getLocaleAccess($locale, $role);
        $access
            ->setViewProducts(true)
            ->setEditProducts($accessLevel === Attributes::EDIT_PRODUCTS);

        $this->getObjectManager()->persist($access);
    }

    /**
     * Revoke access to an attribute locale
     * If $excludedRoles are provided, access will not be revoked for roles with them
     *
     * @param Locale $locale
     * @param Role[] $excludedRoles
     *
     * @return integer
     */
    protected function revokeAccess(Locale $locale, array $excludedRoles = [])
    {
        return $this->getAccessRepository()->revokeAccess($locale, $excludedRoles);
    }

    /**
     * Get LocaleAccess entity for a locale and role
     *
     * @param Locale $locale
     * @param Role   $role
     *
     * @return LocaleAccess
     */
    protected function getLocaleAccess(Locale $locale, Role $role)
    {
        $access = $this->getAccessRepository()
            ->findOneBy(
                [
                    'locale' => $locale,
                    'role'           => $role
                ]
            );

        if (!$access) {
            $access = new $this->localeAccessClass();
            $access
                ->setLocale($locale)
                ->setRole($role);
        }

        return $access;
    }

    /**
     * Get locale access repository
     *
     * @return LocaleAccessRepository
     */
    protected function getAccessRepository()
    {
        return $this->registry->getRepository($this->localeAccessClass);
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->registry->getManagerForClass($this->localeAccessClass);
    }
}
