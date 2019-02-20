<?php

namespace Akeneo\UserManagement\Component\Updater;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;

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

    /** @var ObjectRepository */
    protected $gridViewRepository;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var string */
    protected $fileStorageFolder;

    /** @var string[] */
    private $properties;

    /**
     * @param UserManager                           $userManager
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $roleRepository
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param ObjectRepository                      $gridViewRepository
     * @param FileInfoRepositoryInterface           $fileInfoRepository
     * @param FileStorerInterface                   $fileStorer
     * @param string                                $fileStorageFolder
     */
    public function __construct(
        UserManager $userManager,
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $roleRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ObjectRepository $gridViewRepository,
        FileInfoRepositoryInterface $fileInfoRepository,
        FileStorerInterface $fileStorer,
        string $fileStorageFolder,
        string ...$properties
    ) {
        $this->userManager = $userManager;
        $this->categoryRepository = $categoryRepository;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->roleRepository = $roleRepository;
        $this->groupRepository = $groupRepository;
        $this->gridViewRepository = $gridViewRepository;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->fileStorer = $fileStorer;
        $this->fileStorageFolder = $fileStorageFolder;
        $this->properties = $properties;
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
     *     'tree': 'master'
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
            if ($value !== null) {
                $this->setData($user, $field, $value);
            }
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
            case 'email_notifications':
                $user->setEmailNotifications($data);
                break;
            case 'roles':
                $roles = [];
                foreach ($data as $code) {
                    $roles[] = $this->findRole($code);
                }

                $user->setRoles($roles);
                break;
            case 'groups':
                $groups = [];
                foreach ($data as $code) {
                    $groups[] = $this->findGroup($code);
                }
                $user->setGroups($groups);
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
            case 'avatar':
                $this->setAvatar($user, $data);
                break;
            case 'product_grid_filters':
                if (is_string($data) && '' !== $data) {
                    $user->setProductGridFilters(explode(',', $data));
                    break;
                }
                if (is_array($data) && [] !== $data) {
                    $user->setProductGridFilters($data);
                    break;
                }

                $user->setProductGridFilters([]);
                break;
            case 'properties':
                foreach ($data as $propertyName => $propertyValue) {
                    $user->addProperty($propertyName, $propertyValue);
                }

                break;
            default:
                // For compatibilty
                if (in_array($field, $this->properties)) {
                    $user->addProperty($field, $data);

                    return;
                }

                $matches = null;
                // Example: default_product_grid_view
                if (preg_match('/^default_(?P<alias>[a-z_]+)_view$/', $field, $matches)) {
                    $alias = str_replace('_', '-', $matches['alias']);
                    $user->setDefaultGridView($alias, $this->findDefaultGridView($alias, $data));

                    return;
                }

                throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param string        $code
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
     * @param string $alias
     * @param string $code
     *
     * @throws InvalidPropertyException
     *
     * @return DatagridView|null
     */
    protected function findDefaultGridView($alias, $code): ?DatagridView
    {
        if ($code === '') {
            return null;
        }

        $defaultGridView = $this->gridViewRepository->findOneBy([
            'type' => DatagridView::TYPE_PUBLIC,
            'datagridAlias' => $alias,
            'id' => $code
        ]);

        if (null === $defaultGridView) {
            throw InvalidPropertyException::validEntityCodeExpected(
                sprintf('default_%s_view', $alias),
                'grid view code',
                'The grid view does not exist',
                static::class,
                $code
            );
        }

        return $defaultGridView;
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
     * @return ChannelInterface|null
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
     * @param $user UserInterface
     * @param $data array
     *
     * @throws FileRemovalException
     * @throws FileTransferException
     * @throws \Exception
     */
    private function setAvatar($user, $data)
    {
        $fileInfo = null;

        if ($data['filePath'] !== null && $data['filePath'] !== '') {
            $fileInfo = $this->fileInfoRepository->findOneBy([
                'key' => str_replace($this->fileStorageFolder, '', $data['filePath']),
            ]);

            if (null === $fileInfo) {
                $rawFile = new \SplFileInfo($data['filePath']);
                if (!$rawFile->isFile()) {
                    throw InvalidPropertyException::validPathExpected(
                        'avatar',
                        static::class,
                        $data['filePath']
                    );
                }
                $fileInfo = $this->fileStorer->store($rawFile, 'catalogStorage');
            }
        }

        $user->setAvatar($fileInfo);
    }
}
