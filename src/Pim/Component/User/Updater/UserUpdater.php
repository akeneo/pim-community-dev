<?php

namespace Pim\Component\User\Updater;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\UserBundle\Manager\UserManager;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Pim\Component\User\Model\GroupInterface;
use Pim\Component\User\Model\Role;
use Pim\Component\User\Model\UserInterface;

/**
 * Updates an user
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserUpdater implements ObjectUpdaterInterface
{
    /** @var UserManager */
    protected $userManager;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $categoryRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $roleRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $categoryAssetRepository;

    /**
     * @param UserManager                           $userManager
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $roleRepository
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param IdentifiableObjectRepositoryInterface $categoryAssetRepository
     */
    public function __construct(
        UserManager $userManager,
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $roleRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $categoryAssetRepository = null
    ) {
        $this->userManager = $userManager;
        $this->categoryRepository = $categoryRepository;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->roleRepository = $roleRepository;
        $this->groupRepository = $groupRepository;
        $this->categoryAssetRepository = $categoryAssetRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'code': 'ecommerce',
     *     'label': 'Ecommerce',
     *     'locales': ['en_US'],
     *     'currencies': ['EUR', 'USD'],
     *     'tree': 'master',
     *     'color': 'orange'
     * }
     */
    public function update($user, array $data, array $options = [])
    {
        if (!$user instanceof UserInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($user),
                UserInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($user, $field, $value);
        }

        if (!$user->hasGroup('all')) {
            $user->addGroup($this->findGroup('all'));
        }

        return $this;
    }

    /**
     * @param UserInterface $user
     * @param string        $field
     * @param mixed         $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(UserInterface $user, $field, $data)
    {
        switch ($field) {
            case 'username':
                $user->setUsername($data);
                break;
            case 'name_prefix':
                $user->setNamePrefix($data);
                break;
            case 'first_name':
                $user->setFirstName($data);
                break;
            case 'middle_name':
                $user->setMiddleName($data);
                break;
            case 'last_name':
                $user->setLastName($data);
                break;
            case 'name_suffix':
                $user->setNameSuffix($data);
                break;
            case 'email':
                $user->setEmail($data);
                break;
            case 'password':
                $user->setPlainPassword($data);

                $this->userManager->updatePassword($user);
                break;
            case 'birthday':
                $user->setBirthday(new \DateTime($data, \DateTime::ISO8601));
                break;
            case 'email_notifications':
                $user->setEmailNotifications($data);
                break;
            case 'catalog_default_locale':
                $user->setCatalogLocale($this->findLocale('catalog_default_locale', $data));
                break;
            case 'user_default_locale':
                $user->setUiLocale($this->findLocale('user_default_locale', $data));
                break;
            case 'catalog_default_scope':
                $user->setCatalogScope($this->findChannel($data));
                break;
            case 'default_category_tree':
                $user->setDefaultTree($this->findCategory($data));
                break;
            case 'roles':
                foreach ($data as $code) {
                    $role = $this->findRole($code);
                    $user->addRole($role);
                }
                break;
            case 'groups':
                foreach ($data as $code) {
                    $role = $this->findGroup($code);
                    $user->addGroup($role);
                }
                break;
            case 'phone':
                $user->setPhone($data);
                break;
            case 'enabled':
                $user->setEnabled($data);
                break;
            case 'timezone':
                $user->setTimezone($data);
                break;
            case 'default_asset_tree':
                $user->setDefaultAssetTree($this->findAssetCategory($data));
                break;
            case 'proposals_to_review_notification':
                $user->setProposalsToReviewNotification($data);
                break;
            case 'proposals_state_notifications':
                $user->setProposalsStateNotification($data);
                break;
            default:
                throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param string $code
     *
     * @throws InvalidPropertyException
     *
     * @return CategoryInterface
     */
    protected function findCategory($code)
    {
        $category = $this->categoryRepository->findOneByIdentifier($code);

        if (null === $category) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'default_category_tree',
                'category code',
                'The category does not exist',
                static::class,
                $code
            );
        }

        return $category;
    }

    /**
     * @param string $field
     * @param string $code
     *
     * @throws InvalidPropertyException
     *
     * @return LocaleInterface
     */
    protected function findLocale($field, $code)
    {
        $locale = $this->localeRepository->findOneByIdentifier($code);

        if (null === $locale) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $field,
                'locale code',
                'The locale does not exist',
                static::class,
                $code
            );
        }

        return $locale;
    }

    /**
     * @param string $code
     *
     * @throws InvalidPropertyException
     *
     * @return \Akeneo\Channel\Component\Model\ChannelInterface|null
     */
    protected function findChannel($code)
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);

        if (null === $channel) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'catalog_default_scope',
                'channel code',
                'The channel does not exist',
                static::class,
                $code
            );
        }

        return $channel;
    }

    /**
     * @param string $code
     *
     * @throws InvalidPropertyException
     *
     * @return Role
     */
    protected function findRole($code)
    {
        $role = $this->roleRepository->findOneByIdentifier($code);

        if (null === $role) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'roles',
                'role',
                'The role does not exist',
                static::class,
                $code
            );
        }

        return $role;
    }

    /**
     * @param string $code
     *
     * @throws InvalidPropertyException
     *
     * @return GroupInterface
     */
    protected function findGroup($code)
    {
        $group = $this->groupRepository->findOneByIdentifier($code);

        if (null === $group) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'groups',
                'group',
                'The group does not exist',
                static::class,
                $code
            );
        }

        return $group;
    }

    /**
     * Get tree entity from category code
     *
     * @param string $code
     *
     * @throws InvalidPropertyException
     *
     * @return CategoryInterface
     */
    private function findAssetCategory($code)
    {
        if (null === $this->categoryAssetRepository) {
            throw new \LogicException(
                'The catagory asset repository is not configured yet. Please update the user updater service.'
            );
        }

        $category = $this->categoryAssetRepository->findOneByIdentifier($code);

        if (null === $category) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'default_asset_tree',
                'category code',
                'The category does not exist',
                static::class,
                $code
            );
        }

        return $category;
    }
}
