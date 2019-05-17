<?php

namespace Akeneo\UserManagement\Component\Factory;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Platform\Bundle\UIBundle\UiLocaleProvider;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;

/**
 * Creates and configures a user instance.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserFactory implements SimpleFactoryInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;
    
    /** @var RoleRepositoryInterface */
    protected $roleRepository;

    /** @var string */
    protected $userClass;

    /** @var DefaultProperty[] */
    private $defaultProperties;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param RoleRepositoryInterface $roleRepository
     * @param string $userClass
     * @param DefaultProperty[] $defaultProperties
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepositoryInterface $groupRepository,
        RoleRepositoryInterface $roleRepository,
        string $userClass,
        DefaultProperty ...$defaultProperties
    ) {
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->categoryRepository = $categoryRepository;
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
        $this->userClass = $userClass;
        $this->defaultProperties = $defaultProperties;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $user = new $this->userClass();
        if (null !== $uiLocale = $this->getDefaultUiLocale()) {
            $user->setUiLocale($uiLocale);
        }
        if (null !== $catalogLocale = $this->getDefaultCatalogLocale()) {
            $user->setCatalogLocale($catalogLocale);
        }
        if (null !== $catalogScope = $this->getDefaultCatalogScope()) {
            $user->setCatalogScope($catalogScope);
        }
        if (null !== $categoryTree = $this->getDefaultCategoryTree()) {
            $user->setDefaultTree($categoryTree);
        }
        if (null !== $group = $this->getDefaultGroup()) {
            $user->addGroup($group);
        }
        if (null !== $role = $this->roleRepository->findOneByIdentifier('ROLE_USER')) {
            $user->addRole($role);
        }

        return array_reduce($this->defaultProperties, function ($user, DefaultProperty $defaultProperty) {
            return $defaultProperty->mutate($user);
        }, $user);
    }

    /**
     * @return LocaleInterface|null when we install the pim
     */
    private function getDefaultCatalogLocale(): ?LocaleInterface
    {
        $locales = $this->localeRepository->getActivatedLocales();
        if (count($locales) === 0) {
            return null;
        }

        return $locales[0];
    }

    /**
     * @return LocaleInterface|null when we install the pim
     */
    private function getDefaultUiLocale(): ?LocaleInterface
    {
        return $this->localeRepository->findOneBy(['code' => UiLocaleProvider::MAIN_LOCALE]);
    }

    /**
     * @return ChannelInterface|null when we install the pim
     */
    private function getDefaultCatalogScope(): ?ChannelInterface
    {
        return $this->channelRepository->findOneBy([]);
    }

    /**
     * @return CategoryInterface|null when we install the pim
     */
    private function getDefaultCategoryTree(): ?CategoryInterface
    {
        $trees = $this->categoryRepository->getTrees();
        if (count($trees) === 0) {
            return null;
        }

        return $trees[0];
    }

    /**
     * @return Group|null when we install the pim
     */
    private function getDefaultGroup(): ?Group
    {
        return $this->groupRepository->findOneByIdentifier('all');
    }
}
