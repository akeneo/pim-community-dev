<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\User;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext as BaseUserContext;
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

    /** @var string */
    protected $treeOptionKey;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param LocaleRepositoryInterface     $localeRepository
     * @param ChannelRepositoryInterface    $channelRepository
     * @param CategoryRepositoryInterface   $categoryRepository
     * @param RequestStack                  $requestStack
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CategoryAccessRepository      $categoryAccessRepo
     * @param string                        $defaultLocale
     * @param string                        $treeOptionKey
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker,
        CategoryAccessRepository $categoryAccessRepo,
        $defaultLocale,
        $treeOptionKey
    ) {
        parent::__construct(
            $tokenStorage,
            $localeRepository,
            $channelRepository,
            $categoryRepository,
            $requestStack,
            $defaultLocale
        );

        $this->authorizationChecker = $authorizationChecker;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->treeOptionKey = $treeOptionKey;
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

        if (null === $locale || !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
            $locale = $this->getSessionLocale();
        }

        if (null === $locale || !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
            $locale = $this->getUserLocale();
        }

        if (null === $locale || !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
            $locale = $this->getDefaultLocale();
        }

        if (null === $locale || !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
            $locale = current($this->getGrantedUserLocales());
            $locale = (false === $locale) ? null : $locale;
        }

        if (null === $locale) {
            throw new \LogicException("User doesn't have access to any activated locales");
        }

        if (null !== $this->getCurrentRequest() && $this->getCurrentRequest()->hasSession()) {
            $this->getCurrentRequest()->getSession()->set('dataLocale', $locale->getCode());
            $this->getCurrentRequest()->getSession()->save();
        }

        return $locale;
    }

    /**
     * Returns active locales the user has access to
     *
     * @param string $permissionLevel
     *
     * @return LocaleInterface[]
     */
    public function getGrantedUserLocales($permissionLevel = Attributes::VIEW_ITEMS)
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
        $defaultTree = $this->getUserOption($this->treeOptionKey);

        if (is_string($defaultTree)) {
            $defaultTree = $this->categoryRepository->findOneBy(['code' => $defaultTree]);
        }

        if ($defaultTree && $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $defaultTree)) {
            return $defaultTree;
        }

        $grantedCategoryIds = $this->getGrantedCategories();
        $grantedTrees = $this->categoryRepository->getGrantedTrees($grantedCategoryIds);

        if (!empty($grantedTrees)) {
            return current($grantedTrees);
        }

        throw new \LogicException('User should have a default product tree');
    }

    /**
     * Get the default tree
     *
     * @return CategoryInterface
     */
    public function getDefaultTree()
    {
        $defaultTree = $this->getUserOption($this->treeOptionKey);

        if (is_string($defaultTree)) {
            return $this->categoryRepository->findOneBy(['code' => $defaultTree]);
        }

        return $defaultTree;
    }

    /**
     * Get granted categories
     *
     * @return int[]
     */
    public function getGrantedCategories()
    {
        $user = $this->getUser();

        if (null === $user) {
            return [];
        }

        return $this->categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS);
    }

    /**
     * Get user product category tree
     *
     * @return CategoryInterface
     */
    public function getUserProductCategoryTree(): CategoryInterface
    {
        $defaultTree = $this->getUserOption($this->treeOptionKey);

        if (is_string($defaultTree)) {
            return $this->categoryRepository->findOneBy(['code' => $defaultTree]);
        }

        if ($defaultTree instanceof CategoryInterface) {
            return $defaultTree;
        }

        return current($this->categoryRepository->getTrees());
    }
}
