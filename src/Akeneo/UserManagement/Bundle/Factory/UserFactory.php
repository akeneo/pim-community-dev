<?php

namespace Akeneo\UserManagement\Bundle\Factory;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Pim\Bundle\LocalizationBundle\Provider\UiLocaleProvider;

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

    /**
     * @param LocaleRepositoryInterface   $localeRepository
     * @param ChannelRepositoryInterface  $channelRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param GroupRepositoryInterface    $groupRepository
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->categoryRepository = $categoryRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $user = new User();
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

        return $user;
    }

    private function getDefaultCatalogLocale(): ?LocaleInterface
    {
        $locales = $this->localeRepository->getActivatedLocales();
        if (count($locales) === 0) {
            return null;
        }

        return $locales[0];
    }

    private function getDefaultUiLocale(): ?LocaleInterface
    {
        return $this->localeRepository->findOneBy(['code' => UiLocaleProvider::MAIN_LOCALE]);
    }

    private function getDefaultCatalogScope(): ?ChannelInterface
    {
        return $this->channelRepository->findOneBy([]);
    }

    private function getDefaultCategoryTree(): ?CategoryInterface
    {
        $trees = $this->categoryRepository->getTrees();
        if (count($trees) === 0) {
            return null;
        }

        return $trees[0];
    }

    private function getDefaultGroup(): ?Group
    {
        return $this->groupRepository->findOneByIdentifier('all');
    }
}
