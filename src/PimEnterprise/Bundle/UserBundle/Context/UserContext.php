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
    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param LocaleRepositoryInterface     $localeRepository
     * @param ChannelRepositoryInterface    $channelRepository
     * @param CategoryRepositoryInterface   $categoryRepository
     * @param RequestStack                  $requestStack
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ChoicesBuilderInterface       $choicesBuilder
     * @param string                        $defaultLocale
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker,
        ChoicesBuilderInterface $choicesBuilder,
        $defaultLocale
    ) {
        $this->tokenStorage         = $tokenStorage;
        $this->localeRepository     = $localeRepository;
        $this->channelRepository    = $channelRepository;
        $this->categoryRepository   = $categoryRepository;
        $this->requestStack         = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
        $this->choicesBuilder       = $choicesBuilder;
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
     * Get accessible user category tree
     *
     * @throws \LogicException
     *
     * @return CategoryInterface
     */
    public function getAccessibleUserTree()
    {
//        $defaultTree = $this->getUserOption('defaultTree');
//
//        if ($defaultTree && $this->authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $defaultTree)) {
//            return $defaultTree;
//        }

        // TODO: PIM-4292: add granted categories
        $grantedTrees = $this->categoryRepository->getTrees();
//        $grantedTrees = $this->chainedFilter->filterCollection($trees, 'pim.internal_api.product_category.view');

        if (!empty($grantedTrees)) {
            return current($grantedTrees);
        }

        throw new \LogicException('User should have a default product tree');
    }
}
