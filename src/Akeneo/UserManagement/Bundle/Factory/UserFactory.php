<?php

namespace Akeneo\UserManagement\Bundle\Factory;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
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
        $user->setUiLocale($this->getDefaultUiLocale());
        $user->setCatalogLocale($this->getDefaultCatalogLocale());
        $user->setCatalogScope($this->getDefaultCatalogScope());
        $user->setDefaultTree($this->getDefaultCategoryTree());
        $user->addGroup($this->getDefaultGroup());

        return $user;
    }

    private function getDefaultCatalogLocale(): LocaleInterface
    {
        return $this->localeRepository->getActivatedLocales()[0];
    }

    private function getDefaultUiLocale(): LocaleInterface
    {
        return $this->localeRepository->findOneBy(['code' => UiLocaleProvider::MAIN_LOCALE]);
    }

    private function getDefaultCatalogScope(): ChannelInterface
    {
        return $this->channelRepository->findOneBy([]);
    }

    private function getDefaultCategoryTree(): CategoryInterface
    {
        return $this->categoryRepository->getTrees()[0];
    }

    private function getDefaultGroup()
    {
        return $this->groupRepository->findOneByIdentifier('all');
    }
}
