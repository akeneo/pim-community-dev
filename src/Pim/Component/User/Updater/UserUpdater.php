<?php

namespace Pim\Component\User\Updater;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

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

    /**
     * @param UserManager                           $userManager
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $roleRepository
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     */
    public function __construct(
        UserManager $userManager,
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $roleRepository,
        IdentifiableObjectRepositoryInterface $groupRepository
    ) {
        $this->userManager = $userManager;
        $this->categoryRepository = $categoryRepository;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->roleRepository = $roleRepository;
        $this->groupRepository = $groupRepository;
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
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\UserBundle\Entity\UserInterface", "%s" provided.',
                    ClassUtils::getClass($user)
                )
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
     * @throws \InvalidArgumentException
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
            case 'catalog_locale':
                $user->setCatalogLocale($this->findLocale($data));
                break;
            case 'user_locale':
                $user->setUiLocale($this->findLocale($data));
                break;
            case 'catalog_scope':
                $user->setCatalogScope($this->findChannel($data));
                break;
            case 'default_tree':
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
            case 'api_key':
                if (null === $api = $user->getApi()) {
                    $api = new UserApi();
                }
                $api->setApiKey($data)->setUser($user);
                $user->setApi($api);
                break;
        }
    }

    /**
     * @param string $code
     *
     * @return CategoryInterface|null
     */
    protected function findCategory($code)
    {
        $category = $this->categoryRepository->findOneByIdentifier($code);

        if (null === $category) {
            throw new \InvalidArgumentException(sprintf('Category %s was not found', $code));
        }

        return $category;
    }

    /**
     * @param string $code
     *
     * @return LocaleInterface|null
     */
    protected function findLocale($code)
    {
        $locale = $this->localeRepository->findOneByIdentifier($code);

        if (null === $locale) {
            throw new \InvalidArgumentException(sprintf('Locale %s was not found', $code));
        }

        return $locale;
    }

    /**
     * @param string $code
     *
     * @return ChannelInterface|null
     */
    protected function findChannel($code)
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);

        if (null === $channel) {
            throw new \InvalidArgumentException(sprintf('Channel %s was not found', $code));
        }

        return $channel;
    }

    /**
     * @param string $code
     *
     * @return Role|null
     */
    protected function findRole($code)
    {
        $role = $this->roleRepository->findOneByIdentifier($code);

        if (null === $role) {
            throw new \InvalidArgumentException(sprintf('Role %s was not found', $code));
        }

        return $role;
    }

    /**
     * @param string $code
     *
     * @return Group|null
     */
    protected function findGroup($code)
    {
        $group = $this->groupRepository->findOneByIdentifier($code);

        if (null === $group) {
            throw new \InvalidArgumentException(sprintf('Group %s was not found', $code));
        }

        return $group;
    }
}
