<?php

namespace Akeneo\UserManagement\Component\Model;

use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author    Nicolas Dupont <nicalas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class User implements UserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const GROUP_DEFAULT = 'All';
    const ROLE_ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';
    const DEFAULT_TIMEZONE = 'UTC';
    const TYPE_USER = 'user';
    const TYPE_API = 'api';

    /** @var int|string */
    protected $id;

    /** @var string */
    protected $username;

    /** @var string */
    protected $email;

    /** @var string */
    protected $namePrefix;

    /** @var string */
    protected $firstName;

    /** @var string */
    protected $middleName;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $nameSuffix;

    /**
     * Image filename
     *
     * @var string
     */
    protected $image;

    /** @var FileInfoInterface */
    protected $avatar;

    /**
     * Image filename
     *
     * @var UploadedFile
     */
    protected $imageFile;

    /** @var boolean */
    protected $enabled = true;

    /**
     * The salt to use for hashing
     *
     * @var string
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     */
    protected $confirmationToken;

    /** @var \DateTime */
    protected $passwordRequestedAt;

    /** @var \DateTime */
    protected $lastLogin;

    /** @var int */
    protected $loginCount = 0;

    /** @var Role[] */
    protected $roles;

    /** @var GroupInterface[] */
    protected $groups;

    /** @var string */
    protected $api;

    /** @var \DateTime $createdAt */
    protected $createdAt;

    /** @var \DateTime $updatedAt */
    protected $updatedAt;

    /** @var LocaleInterface */
    protected $catalogLocale;

    /** @var LocaleInterface */
    protected $uiLocale;

    /** @var ChannelInterface */
    protected $catalogScope;

    /** @var CategoryInterface */
    protected $defaultTree;

    /** @var ArrayCollection */
    protected $defaultGridViews;

    /** @var bool */
    protected $emailNotifications = false;

    /** @var array */
    protected $productGridFilters = [];

    /** @var string */
    protected $phone;

    /** @var string */
    protected $timezone;

    /** @var array $property bag for properties extension */
    private $properties = [];

    protected $type = self::TYPE_USER;

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->defaultGridViews = new ArrayCollection();
        $this->timezone = self::DEFAULT_TIMEZONE;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->password,
                $this->salt,
                $this->username,
                $this->enabled,
                $this->confirmationToken,
                $this->id,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list(
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->confirmationToken,
            $this->id
        ) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return UserInterface::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamePrefix(): string
    {
        return $this->namePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameSuffix(): string
    {
        return $this->nameSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullName(): string
    {
        return implode(' ', [
            $this->namePrefix,
            $this->firstName,
            $this->middleName,
            $this->lastName,
            $this->nameSuffix
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage(): object
    {
        return $this->image;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageFile(): \Symfony\Component\HttpFoundation\File\UploadedFile
    {
        return $this->imageFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvatar(): ?FileInfoInterface
    {
        return $this->avatar;
    }

    /**
     * {@inheritdoc}
     */
    public function setAvatar(?FileInfoInterface $avatar = null): void
    {
        $this->avatar = $avatar;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken(): string
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastLogin(): \DateTime
    {
        return $this->lastLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginCount(): int
    {
        return $this->loginCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked(): bool
    {
        return $this->isEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * {@inheritdoc}
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername(string $username): UserInterface
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstName(string $firstName = null): UserInterface
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastName(string $lastName = null): UserInterface
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddleName(string $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * {@inheritdoc}
     */
    public function setNamePrefix(string $namePrefix): void
    {
        $this->namePrefix = $namePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function setNameSuffix(string $nameSuffix): void
    {
        $this->nameSuffix = $nameSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function setImage(string $image = null): UserInterface
    {
        $this->image = $image;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setImageFile(UploadedFile $imageFile): UserInterface
    {
        $this->imageFile = $imageFile;
        // this will trienvogger PreUpdate callback even if only image has been changed
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetImageFile(): object
    {
        $this->imageFile = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled(bool $enabled): UserInterface
    {
        $this->enabled = (boolean) $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalt(string $salt): UserInterface
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword(string $password): UserInterface
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlainPassword(string $password): UserInterface
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfirmationToken(string $token): UserInterface
    {
        $this->confirmationToken = $token;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPasswordRequestedAt(\DateTime $time = null): UserInterface
    {
        $this->passwordRequestedAt = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastLogin(\DateTime $time): UserInterface
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLoginCount(int $count): UserInterface
    {
        $this->loginCount = $count;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt): UserInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt): UserInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = $this->roles->toArray();

        /** @var GroupInterface $group */
        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles()->toArray());
        }

        return array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function getRolesCollection(): Collection
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole(string $roleName): ?\Akeneo\UserManagement\Component\Model\Role
    {
        /** @var Role $item */
        foreach ($this->getRoles() as $item) {
            if ($roleName == $item->getRole()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role): bool
    {
        if ($role instanceof Role) {
            $roleName = $role->getRole();
        } elseif (is_string($role)) {
            $roleName = $role;
        } else {
            throw new \InvalidArgumentException(
                sprintf('$role must be an instance of %s or a string', Role::class)
            );
        }

        return (bool) $this->getRole($roleName);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(Role $role): UserInterface
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role): void
    {
        if ($role instanceof Role) {
            $roleObject = $role;
        } elseif (is_string($role)) {
            $roleObject = $this->getRole($role);
        } else {
            throw new \InvalidArgumentException(
                sprintf('$role must be an instance of %s or a string', Role::class)
            );
        }
        if ($roleObject !== null) {
            $this->roles->removeElement($roleObject);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(Collection $roles): UserInterface
    {
        if (!$roles instanceof Collection && !is_array($roles)) {
            throw new \InvalidArgumentException(
                '$roles must be an instance of Doctrine\Common\Collections\Collection or an array'
            );
        }

        $this->roles->clear();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRolesCollection(Collection $collection): UserInterface
    {
        if (!$collection instanceof Collection) {
            throw new \InvalidArgumentException(
                '$collection must be an instance of Doctrine\Common\Collections\Collection'
            );
        }
        $this->roles = $collection;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupNames(): array
    {
        $names = [];

        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup(string $name): bool
    {
        return in_array($name, $this->getGroupNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(GroupInterface $group): UserInterface
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(GroupInterface $group): UserInterface
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroups(array $groups): UserInterface
    {
        $this->groups->clear();

        foreach ($groups as $group) {
            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupsIds(): array
    {
        $ids = [];
        foreach ($this->groups as $group) {
            $ids[] = $group->getId();
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getImagePath(): ?string
    {
        if ($this->image !== '') {
            return $this->getUploadDir(true) . '/' . $this->image;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generateToken(): string
    {
        return base_convert(bin2hex(hash('sha256', uniqid(mt_rand(), true), true)), 16, 36);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(): void
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->loginCount = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadDir($forWeb = false): string
    {
        $ds = DIRECTORY_SEPARATOR;

        if ($forWeb) {
            $ds = '/';
        }

        $suffix = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m') : date('Y-m');

        return ($forWeb ? $ds : '').'uploads'.$ds.'users'.$ds.$suffix;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogLocale(): \Akeneo\Channel\Component\Model\LocaleInterface
    {
        return $this->catalogLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setCatalogLocale(LocaleInterface $catalogLocale): UserInterface
    {
        $this->catalogLocale = $catalogLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUiLocale(): \Akeneo\Channel\Component\Model\LocaleInterface
    {
        return $this->uiLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setUiLocale(LocaleInterface $uiLocale): UserInterface
    {
        $this->uiLocale = $uiLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogScope(): \Akeneo\Channel\Component\Model\ChannelInterface
    {
        return $this->catalogScope;
    }

    /**
     * {@inheritdoc}
     */
    public function setCatalogScope(ChannelInterface $catalogScope): UserInterface
    {
        $this->catalogScope = $catalogScope;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTree(): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        return $this->defaultTree;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultTree(CategoryInterface $defaultTree): UserInterface
    {
        $this->defaultTree = $defaultTree;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailNotifications(): bool
    {
        return $this->emailNotifications;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailNotifications(bool $emailNotifications): UserInterface
    {
        $this->emailNotifications = $emailNotifications;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductGridFilters(): array
    {
        return $this->productGridFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductGridFilters(array $productGridFilters = []): UserInterface
    {
        $this->productGridFilters = $productGridFilters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGridView($alias): ?DatagridView
    {
        foreach ($this->defaultGridViews as $datagridView) {
            if ($datagridView->getDatagridAlias() === $alias) {
                return $datagridView;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGridViews(): array
    {
        $views = [];
        foreach ($this->defaultGridViews as $datagridView) {
            $views[$datagridView->getDatagridAlias()] = $datagridView;
        }

        return $views;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultGridView(string $alias, ?DatagridView $defaultGridView): UserInterface
    {
        if (null !== $gridView = $this->getDefaultGridView($alias)) {
            $this->defaultGridViews->removeElement($gridView);
        }

        if (null !== $defaultGridView) {
            $this->defaultGridViews->set($alias, $defaultGridView);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * {@inheritdoc}
     */
    public function setPhone(?string $phone): UserInterface
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function setTimezone(string $timezone): UserInterface
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function isApiUser(): bool
    {
        return self::TYPE_API === $this->type;
    }

    public function defineAsApiUser(): void
    {
        $this->type = self::TYPE_API;
    }

    /**
     * {@inheritdoc}
     */
    public function addProperty(string $propertyName, $propertyValue): void
    {
        $propertyName = Inflector::tableize($propertyName);

        $this->properties[$propertyName] = $propertyValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty(string $propertyName)
    {
        $propertyName = Inflector::tableize($propertyName);

        return $this->properties[$propertyName] ?? null;
    }
}
