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

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface as CatalogCategoryInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\UserBundle\Context\UserContext as BaseUserContext;
use Pim\Component\Classification\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * User context that provides access to user locale, channel and default category tree
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class UserContext extends BaseUserContext
{
    /** @staticvar string */
    const USER_ASSET_CATEGORY_TYPE = 'asset';

    /** @staticvar string */
    const USER_PUBLISHED_PRODUCT_CATEGORY_TYPE = 'published_product';

    /** @var CategoryManager */
    protected $categoryManager;

    /** @var CategoryRepositoryInterface */
    protected $assetCategoryRepo;

    /**
     * @param SecurityContextInterface    $securityContext
     * @param LocaleManager               $localeManager
     * @param ChannelManager              $channelManager
     * @param CategoryRepositoryInterface $productCategoryRepo
     * @param CategoryRepositoryInterface $assetCategoryRepo
     * @param string                      $defaultLocale
     */
    function __construct(
        SecurityContextInterface $securityContext,
        LocaleManager $localeManager,
        ChannelManager $channelManager,
        CategoryRepositoryInterface $productCategoryRepo,
        CategoryRepositoryInterface $assetCategoryRepo,
        $defaultLocale
    ) {
        parent::__construct($securityContext, $localeManager, $channelManager, $productCategoryRepo, $defaultLocale);

        $this->assetCategoryRepo = $assetCategoryRepo;
    }

    /**
     * Returns the current locale making sure that user has permissions for this locale
     *
     * @throws \LogicException When there is no granted locale
     *
     * @return LocaleInterface
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
     * @return LocaleInterface[]
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
     * @return CatalogCategoryInterface
     *
     * @deprecated Will be removed in 1.5. Please use getAccessibleUserProductCategoryTree() instead.
     */
    public function getAccessibleUserTree()
    {
        return $this->getAccessibleUserProductCategoryTree();
    }

    /**
     * @param string $relatedEntity
     *
     * @return CategoryInterface|null
     */
    public function getAccessibleUserCategoryTree($relatedEntity)
    {
        switch ($relatedEntity) {
            case static::USER_PRODUCT_CATEGORY_TYPE:
            case static::USER_PUBLISHED_PRODUCT_CATEGORY_TYPE:
                return $this->getAccessibleUserProductCategoryTree();
            case static::USER_ASSET_CATEGORY_TYPE:
                return $this->getAccessibleUserAssetCategoryTree();
        }

        return null;
    }

    /**
     * Get accessible user product category tree
     *
     * @throws \LogicException
     *
     * @return CategoryInterface
     */
    public function getAccessibleUserProductCategoryTree()
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

    /**
     * @return CategoryInterface
     */
    public function getAccessibleUserAssetCategoryTree()
    {
        return $this->assetCategoryRepo->findOneBy(['id' => 1]);
    }
}
