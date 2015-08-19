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

use Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface;
use Pim\Bundle\CatalogBundle\Filter\ChainedFilter;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface as CatalogCategoryInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext as BaseUserContext;
use Pim\Component\Classification\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    /** @var CategoryRepositoryInterface */
    protected $assetCategoryRepo;

    /** @var ChainedFilter */
    protected $chainedFilter;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param LocaleRepositoryInterface     $localeRepository
     * @param ChannelRepositoryInterface    $channelRepository
     * @param CategoryRepositoryInterface   $productCategoryRepo
     * @param CategoryRepositoryInterface   $assetCategoryRepo
     * @param ChainedFilter                 $chainedFilter
     * @param RequestStack                  $requestStack
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $defaultLocale
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $productCategoryRepo,
        CategoryRepositoryInterface $assetCategoryRepo,
        ChainedFilter $chainedFilter,
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker,
        ChoicesBuilderInterface $choicesBuilder,
        $defaultLocale
    ) {
        parent::__construct(
            $tokenStorage,
            $localeRepository,
            $channelRepository,
            $productCategoryRepo,
            $requestStack,
            $choicesBuilder,
            $defaultLocale
        );

        $this->assetCategoryRepo    = $assetCategoryRepo;
        $this->chainedFilter        = $chainedFilter;
        $this->authorizationChecker = $authorizationChecker;
        $this->defaultLocale        = $defaultLocale;
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
        if (null !== $locale && $this->authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
            return $locale;
        }

        $locale = $this->getUserLocale();
        if (null !== $locale && $this->authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
            return $locale;
        }

        $locale = $this->getDefaultLocale();
        if (null !== $locale && $this->authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
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
                return $this->authorizationChecker->isGranted($permissionLevel, $locale);
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
     *
     * TODO: In permission reunification (PIM-4292), remove dedicated method and use a single
     *       method to get trees then filter with the right filter.
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

        if ($defaultTree && $this->authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $defaultTree)) {
            return $defaultTree;
        }

        $trees = $this->productCategoryRepo->getTrees();
        $grantedTrees = $this->chainedFilter->filterCollection($trees, 'pim.internal_api.product_category.view');

        if (!empty($grantedTrees)) {
            return current($grantedTrees);
        }

        throw new \LogicException('User should have a default product tree');
    }

    /**
     * Get accessible user asset category tree
     *
     * @throws \LogicException
     *
     * @return CategoryInterface
     */
    public function getAccessibleUserAssetCategoryTree()
    {
        // TODO: Get the default asset category tree

        $trees = $this->assetCategoryRepo->getTrees();
        $grantedTrees = $this->chainedFilter->filterCollection($trees, 'pim.internal_api.asset_category.view');

        if (!empty($grantedTrees)) {
            return current($grantedTrees);
        }

        throw new \LogicException('User should have a default asset tree');
    }
}
