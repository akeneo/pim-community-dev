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
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
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
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param LocaleRepositoryInterface     $localeRepository
     * @param ChannelRepositoryInterface    $channelRepository
     * @param CategoryRepositoryInterface   $categoryRepository
     * @param RequestStack                  $requestStack
     * @param ChoicesBuilderInterface       $choicesBuilder
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CategoryAccessRepository      $categoryAccessRepo
     * @param string                        $defaultLocale
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        RequestStack $requestStack,
        ChoicesBuilderInterface $choicesBuilder,
        AuthorizationCheckerInterface $authorizationChecker,
        CategoryAccessRepository $categoryAccessRepo,
        $defaultLocale
    ) {
        $this->tokenStorage         = $tokenStorage;
        $this->localeRepository     = $localeRepository;
        $this->channelRepository    = $channelRepository;
        $this->categoryRepository   = $categoryRepository;
        $this->requestStack         = $requestStack;
        $this->choicesBuilder       = $choicesBuilder;
        $this->authorizationChecker = $authorizationChecker;
        $this->defaultLocale        = $defaultLocale;
        $this->categoryAccessRepo   = $categoryAccessRepo;
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
//        if ($defaultTree && $this->authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $defaultTree)) {
//            return $defaultTree;
//        }

        $grantedCategoryIds = $this->getGrantedCategories();
        $grantedTrees = $this->categoryRepository->getGrantedTrees($grantedCategoryIds);

        if (!empty($grantedTrees)) {
            return current($grantedTrees);
        }

        throw new \LogicException('User should have a default product tree');
    }

    /**
     * Get granted categories
     *
     * @return integer[]
     */
    protected function getGrantedCategories()
    {
        $user = $this->getUser();

        if (null === $user) {
            return [];
        }

        return $this->categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS);
    }
}
