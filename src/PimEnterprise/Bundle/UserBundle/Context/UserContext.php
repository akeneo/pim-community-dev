<?php

namespace PimEnterprise\Bundle\UserBundle\Context;

use Pim\Bundle\UserBundle\Context\UserContext as BaseUserContext;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * User context that provides access to user locale, channel and default category tree
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class UserContext extends BaseUserContext
{
    /**
     * @var CategoryManager
     */
    protected $categoryManager;

   /**
     * {@inheritdoc}
     *
     * @throws \LogicException When user doesn't have access to any activated locales
     */
    public function getCurrentLocale()
    {
        try {
            return parent::getCurrentLocale();
        } catch (\LogicException $e) {
            throw new \LogicException("User doesn't have access to any activated locales");
        }
    }

    /**
     * Returns active locales the user has access to
     *
     * {@inheritdoc}
     */
    public function getUserLocales()
    {
        if ($this->userLocales === null) {
            $this->userLocales = array_filter(
                parent::getUserLocales(),
                function ($locale) {
                    return $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale);
                }
            );
        }

        return $this->userLocales;
    }

   /**
     * Returns the default application locale if user has access to it
     *
     * {@inheritdoc}
     */
    protected function getDefaultLocale()
    {
        $locale = parent::getDefaultLocale();

        if (null !== $locale && !$this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
            return null;
        }
    }

    /**
     * Checks if a locale is activated and user has the right to access it
     *
     * {@inheritdoc}
     */
    protected function isLocaleAvailable(Locale $locale)
    {
        return parent::isLocaleAvailable($locale) &&
               $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale);
    }

    /**
     * Get user category tree
     *
     * @return CategoryInterface
     * @throws \LogicException
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
