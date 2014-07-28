<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
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
     * Get user groups that have view access to a locale
     *
     * @param Locale $locale
     *
     * @return Group[]
     */
    public function getViewUserGroups(Locale $locale)
    {
        return $this->getAccessRepository()->getGrantedUserGroups($locale, Attributes::VIEW_PRODUCTS);
    }

    /**
     * Get user groups that have edit access to a locale
     *
     * @param Locale $locale
     *
     * @return Group[]
     */
    public function getEditUserGroups(Locale $locale)
    {
        return $this->getAccessRepository()->getGrantedUserGroups($locale, Attributes::EDIT_PRODUCTS);
    }

    /**
     * Grant access on an attribute locale to specified user groups
     *
     * @param Locale  $locale
     * @param Group[] $viewUserGroups
     * @param Group[] $editUserGroups
     */
    public function setAccess(Locale $locale, $viewUserGroups, $editUserGroups)
    {
        $grantedUserGroups = [];
        foreach ($editUserGroups as $group) {
            $this->grantAccess($locale, $group, Attributes::EDIT_PRODUCTS);
            $grantedUserGroups[] = $group;
        }

        foreach ($viewUserGroups as $group) {
            if (!in_array($group, $grantedUserGroups)) {
                $this->grantAccess($locale, $group, Attributes::VIEW_PRODUCTS);
                $grantedUserGroups[] = $group;
            }
        }

        $this->revokeAccess($locale, $grantedUserGroups);
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on an attribute locale for the provided user group
     *
     * @param Locale $locale
     * @param Group  $group
     * @param string $accessLevel
     */
    public function grantAccess(Locale $locale, Group $group, $accessLevel)
    {
        $access = $this->getLocaleAccess($locale, $group);
        $access
            ->setViewProducts(true)
            ->setEditProducts($accessLevel === Attributes::EDIT_PRODUCTS);

        $this->getObjectManager()->persist($access);
    }

    /**
     * Revoke access to an attribute locale
     * If $excludedUserGroups are provided, access will not be revoked for user groups with them
     *
     * @param Locale  $locale
     * @param Group[] $excludedUserGroups
     *
     * @return integer
     */
    protected function revokeAccess(Locale $locale, array $excludedUserGroups = [])
    {
        return $this->getAccessRepository()->revokeAccess($locale, $excludedUserGroups);
    }

    /**
     * Get LocaleAccess entity for a locale and user group
     *
     * @param Locale $locale
     * @param Group  $group
     *
     * @return LocaleAccess
     */
    protected function getLocaleAccess(Locale $locale, Group $group)
    {
        $access = $this->getAccessRepository()
            ->findOneBy(
                [
                    'locale'    => $locale,
                    'userGroup' => $group
                ]
            );

        if (!$access) {
            $access = new $this->localeAccessClass();
            $access
                ->setLocale($locale)
                ->setUserGroup($group);
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
