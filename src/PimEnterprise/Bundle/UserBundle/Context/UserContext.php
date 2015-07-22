<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\UserBundle\Context;

use Pim\Bundle\UserBundle\Context\UserContext as BaseUserContext;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * User context that provides access to user locale, channel and default category tree
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 */
class UserContext extends BaseUserContext
{
    /**
     * @var CategoryManager
     */
    protected $categoryManager;

    /**
     * Returns the current locale making sure that user has permissions for this locale
     *
     * @throws \LogicException When there is no granted locale
     *
     * @return Locale
     */
    public function getCurrentGrantedLocale()
    {
        $locale = $this->getRequestLocale();
        if (null !== $locale && $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
            return $locale;
        }

        $locale = $this->getUserLocale();
        if (null !== $locale && $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
            return $locale;
        }

        $locale = $this->getDefaultLocale();
        if (null !== $locale && $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
            return $locale;
        }

        if ($locale = current($this->getGrantedUserLocales())) {
            return $locale;
        }

        throw new \LogicException("User doesn't have access to any activated locales");
    }

    /**
     * Returns active locales the user has access to
     *
     * @param string $permissionLevel
     *
     * @return Locale[]
     */
    public function getGrantedUserLocales($permissionLevel = Attributes::VIEW_PRODUCTS)
    {
        return array_filter(
            $this->getUserLocales(),
            function ($locale) use ($permissionLevel) {
                return $this->securityContext->isGranted($permissionLevel, $locale);
            }
        );
    }

    /**
     * Get user category tree
     *
     * @throws \LogicException
     *
     * @return CategoryInterface
     */
    public function getAccessibleUserTree()
    {
        $defaultTree = $this->getUserOption('defaultTree');

        if ($defaultTree && $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $defaultTree)) {
            return $defaultTree;
        }

        $trees = $this->categoryManager->getAccessibleTrees($this->getUser());

        if (count($trees)) {
            return current($trees);
        }

        throw new \LogicException('User should have a default tree');
    }
}
