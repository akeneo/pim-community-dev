<?php

namespace Akeneo\UserManagement\Component\Model;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\UserManagement\Component\EntityUploadedImageInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Interface UserInterface
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserInterface extends AdvancedUserInterface, \Serializable, EntityUploadedImageInterface
{
    public const SYSTEM_USER_NAME = 'system';

    /**
     * Get entity class name.
     */
    public function getClass(): string;

    /**
     * Returns the user unique id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * {@inheritDoc}
     */
    public function getEmail();

    /**
     * {@inheritDoc}
     */
    public function getPlainPassword();

    /**
     * {@inheritDoc}
     */
    public function getConfirmationToken();

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt(): ?\DateTime;

    /**
     * Gets the last login time.
     */
    public function getLastLogin(): \DateTime;

    /**
     * Gets login count number.
     */
    public function getLoginCount(): int;

    /**
     * Get user created date/time
     */
    public function getCreatedAt(): \DateTime;

    /**
     * Get user last update date/time
     */
    public function getUpdatedAt(): \DateTime;

    /**
     * @param int $ttl
     */
    public function isPasswordRequestNonExpired(int $ttl): bool;

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function setId(int $id);

    /**
     * @param string $username New username
     */
    public function setUsername(string $username): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param string $email New email value
     */
    public function setEmail(string $email): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param string $firstName [optional] New first name value. Null by default.
     */
    public function setFirstName(string $firstName = null): \Akeneo\UserManagement\Component\Model\UserInterface;

    public function getFirstName(): string;

    /**
     * @param  string $lastName [optional] New last name value. Null by default.
     */
    public function setLastName(string $lastName = null): \Akeneo\UserManagement\Component\Model\UserInterface;

    public function getLastName(): string;

    /**
     * Set middle name
     *
     * @param string $middleName
     */
    public function setMiddleName(string $middleName);

    public function getMiddleName(): string;

    /**
     * Set name prefix
     *
     * @param string $namePrefix
     */
    public function setNamePrefix(string $namePrefix);

    public function getNameSuffix(): string;

    public function getNamePrefix(): string;

    /**
     * Set name suffix
     *
     * @param string $nameSuffix
     */
    public function setNameSuffix(string $nameSuffix);

    /**
     * Get full name with prefix, first, middle, last suffix names
     */
    public function getFullName(): string;

    /**
     * @param  string $image [optional] New image file name. Null by default.
     */
    public function setImage(string $image = null): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param  UploadedFile $imageFile
     */
    public function setImageFile(UploadedFile $imageFile): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @return FileInfoInterface|null
     */
    public function getAvatar(): ?FileInfoInterface;

    /**
     * @param FileInfoInterface|null $avatar
     */
    public function setAvatar(?FileInfoInterface $avatar = null): void;

    /**
     * @param  bool $enabled User state
     */
    public function setEnabled(bool $enabled): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param string $salt
     */
    public function setSalt(string $salt): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param  string $password New encoded password
     */
    public function setPassword(string $password): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param  string $password New password as plain string
     */
    public function setPlainPassword(string $password): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * Set confirmation token.
     *
     * @param  string $token
     */
    public function setConfirmationToken(string $token): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param  \DateTime $time [optional] New password request time. Null by default.
     */
    public function setPasswordRequestedAt(\DateTime $time = null): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param  \DateTime $time New login time
     */
    public function setLastLogin(\DateTime $time): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param  int $count New login count value
     */
    public function setLoginCount(int $count): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt): self;

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt): self;

    /**
     * Returns the true Collection of Roles.
     */
    public function getRolesCollection(): \Doctrine\Common\Collections\Collection;

    /**
     * Pass a string, get the desired Role object or null
     *
     * @param  string $roleName Role name
     */
    public function getRole(string $roleName): ?\Akeneo\UserManagement\Component\Model\Role;

    /**
     * Never use this to check if this user has access to anything!
     * Use the AuthorizationChecker, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $authorizationChecker->isGranted('ROLE_USER');
     *
     * @param  Role|string $role
     *
     * @throws \InvalidArgumentException
     */
    public function hasRole($role): bool;

    /**
     * Adds a Role to the Collection.
     *
     * @param  Role $role
     */
    public function addRole(Role $role): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * Remove the Role object from collection
     *
     * @param  Role|string $role
     *
     * @throws \InvalidArgumentException
     */
    public function removeRole($role);

    /**
     * Pass an array or Collection of Role objects and re-set roles collection with new Roles.
     * Type hinted array due to interface.
     *
     * @param  array|Collection $roles Array of Role objects
     *
     * @throws \InvalidArgumentException
     */
    public function setRoles(\Doctrine\Common\Collections\Collection $roles): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * Directly set the Collection of Roles.
     *
     * @param  Collection $collection
     *
     * @throws \InvalidArgumentException
     */
    public function setRolesCollection(Collection $collection): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * Gets the groups granted to the user
     */
    public function getGroups(): \Doctrine\Common\Collections\Collection;

    public function getGroupNames(): array;

    /**
     * @param  string $name
     */
    public function hasGroup(string $name): bool;

    /**
     * @param  GroupInterface $group
     */
    public function addGroup(GroupInterface $group): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param  GroupInterface $group
     */
    public function removeGroup(GroupInterface $group): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param GroupInterface[] $groups
     */
    public function setGroups(array $groups): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * Get groups ids
     */
    public function getGroupsIds(): array;

    /**
     * @return string|null
     */
    public function getImagePath(): ?string;

    /**
     * Generate unique confirmation token
     *
     * @return string Token value
     */
    public function generateToken(): string;

    /**
     * Pre persist event listener
     */
    public function beforeSave();

    /**
     * Invoked before the entity is updated.
     */
    public function preUpdate();

    public function getCatalogLocale(): \Akeneo\Channel\Component\Model\LocaleInterface;

    /**
     * @param LocaleInterface $catalogLocale
     */
    public function setCatalogLocale(LocaleInterface $catalogLocale): \Akeneo\UserManagement\Component\Model\UserInterface;

    public function getUiLocale(): \Akeneo\Channel\Component\Model\LocaleInterface;

    /**
     * @param LocaleInterface $uiLocale
     */
    public function setUiLocale(LocaleInterface $uiLocale): \Akeneo\UserManagement\Component\Model\UserInterface;

    public function getCatalogScope(): \Akeneo\Channel\Component\Model\ChannelInterface;

    /**
     * @param ChannelInterface $catalogScope
     */
    public function setCatalogScope(ChannelInterface $catalogScope): \Akeneo\UserManagement\Component\Model\UserInterface;

    public function getDefaultTree(): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    /**
     * @param array $productGridFilters
     */
    public function setProductGridFilters(array $productGridFilters = []): \Akeneo\UserManagement\Component\Model\UserInterface;

    public function getProductGridFilters(): array;

    /**
     * @param CategoryInterface $defaultTree
     */
    public function setDefaultTree(CategoryInterface $defaultTree): \Akeneo\UserManagement\Component\Model\UserInterface;

    public function isEmailNotifications(): bool;
    /**
     * @param bool $emailNotifications
     */
    public function setEmailNotifications(bool $emailNotifications): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param string the view alias
     */
    public function getDefaultGridView($alias): ?DatagridView;

    /**
     * Get all default datagrid views
     *
     * @return DatagridView[]
     */
    public function getDefaultGridViews(): array;

    /**
     * @param string            $alias
     * @param DatagridView|null $defaultGridView
     */
    public function setDefaultGridView(string $alias, ?DatagridView $defaultGridView): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @return null|string
     */
    public function getPhone(): ?string;

    /**
     * @param string $phone
     *
     * @return UserInterface
     */
    public function setPhone(?string $phone): UserInterface;

    /**
     * Return the User timezone
     *
     * @return string
     */
    public function getTimezone(): string;

    /**
     * @param string $timezone
     *
     * @return UserInterface
     */
    public function setTimezone(string $timezone): UserInterface;

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    public function addProperty(string $propertyName, $propertyValue): void;

    /**
     * @param string $propertyName
     *
     * @return mixed
     */
    public function getProperty(string $propertyName);

    public function isApiUser(): bool;

    public function defineAsApiUser(): void;
}
