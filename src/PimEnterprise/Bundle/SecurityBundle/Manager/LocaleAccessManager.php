<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\LocaleAccess;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository;
use PimEnterprise\Component\Security\Attributes;

/**
 * Locale access manager
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class LocaleAccessManager
{
    /** @var LocaleAccessRepository */
    protected $repository;

    /** @var BulkSaverInterface */
    protected $saver;

    /** @var string */
    protected $localeAccessClass;

    /**
     * @param LocaleAccessRepository $repository
     * @param BulkSaverInterface     $saver
     * @param string                 $localeAccessClass
     */
    public function __construct(LocaleAccessRepository $repository, BulkSaverInterface $saver, $localeAccessClass)
    {
        $this->repository = $repository;
        $this->saver = $saver;
        $this->localeAccessClass = $localeAccessClass;
    }

    /**
     * Get user groups that have view access to a locale
     *
     * @param LocaleInterface $locale
     *
     * @return GroupInterface[]
     */
    public function getViewUserGroups(LocaleInterface $locale)
    {
        return $this->repository->getGrantedUserGroups($locale, Attributes::VIEW_ITEMS);
    }

    /**
     * Get user groups that have edit access to a locale
     *
     * @param LocaleInterface $locale
     *
     * @return GroupInterface[]
     */
    public function getEditUserGroups(LocaleInterface $locale)
    {
        return $this->repository->getGrantedUserGroups($locale, Attributes::EDIT_ITEMS);
    }

    /**
     * Grant access on an attribute locale to specified user groups
     *
     * @param LocaleInterface  $locale
     * @param GroupInterface[] $viewUserGroups
     * @param GroupInterface[] $editUserGroups
     */
    public function setAccess(LocaleInterface $locale, $viewUserGroups, $editUserGroups)
    {
        $grantedAccesses = [];
        $grantedUserGroups = [];
        foreach ($editUserGroups as $group) {
            $grantedAccesses[] = $this->builGrantAccess($locale, $group, Attributes::EDIT_ITEMS);
            $grantedUserGroups[] = $group;
        }

        foreach ($viewUserGroups as $group) {
            if (!in_array($group, $grantedUserGroups)) {
                $grantedAccesses[] = $this->builGrantAccess($locale, $group, Attributes::VIEW_ITEMS);
                $grantedUserGroups[] = $group;
            }
        }

        $this->revokeAccess($locale, $grantedUserGroups);
        $this->saver->saveAll($grantedAccesses);
    }

    /**
     * Grant specified access on an attribute locale for the provided user group
     *
     * @param LocaleInterface $locale
     * @param GroupInterface  $group
     * @param string          $accessLevel
     */
    public function grantAccess(LocaleInterface $locale, GroupInterface $group, $accessLevel)
    {
        $access = $this->builGrantAccess($locale, $group, $accessLevel);
        $this->saver->saveAll([$access]);
    }

    /**
     * Revoke access to an attribute locale
     * If $excludedUserGroups are provided, access will not be revoked for user groups with them
     *
     * @param LocaleInterface  $locale
     * @param GroupInterface[] $excludedUserGroups
     *
     * @return int
     */
    public function revokeAccess(LocaleInterface $locale, array $excludedUserGroups = [])
    {
        return $this->repository->revokeAccess($locale, $excludedUserGroups);
    }

    /**
     * Get LocaleAccess entity for a locale and user group
     *
     * @param LocaleInterface $locale
     * @param GroupInterface  $group
     *
     * @return \PimEnterprise\Bundle\SecurityBundle\Entity\LocaleAccess
     */
    protected function getLocaleAccess(LocaleInterface $locale, GroupInterface $group)
    {
        $access = $this->repository
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
     * Build specified access on an attribute locale for the provided user group
     *
     * @param LocaleInterface $locale
     * @param GroupInterface  $group
     * @param string          $accessLevel
     *
     * @return LocaleAccess
     */
    protected function builGrantAccess(LocaleInterface $locale, GroupInterface $group, $accessLevel)
    {
        $access = $this->getLocaleAccess($locale, $group);
        $access
            ->setViewProducts(true)
            ->setEditProducts($accessLevel === Attributes::EDIT_ITEMS);

        return $access;
    }
}
