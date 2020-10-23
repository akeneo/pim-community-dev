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
     *
     * @return string
     */
    public function getClass();

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
    public function getPasswordRequestedAt();

    /**
     * Gets the last login time.
     *
     * @return \DateTime
     */
    public function getLastLogin();

    /**
     * Gets login count number.
     *
     * @return int
     */
    public function getLoginCount();

    /**
     * Get user created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Get user last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param int $ttl
     *
     * @return bool
     */
    public function isPasswordRequestNonExpired($ttl);

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function setId($id);

    /**
     * @param string $username New username
     *
     * @return UserInterface
     */
    public function setUsername($username);

    /**
     * @param string $email New email value
     *
     * @return UserInterface
     */
    public function setEmail($email);

    /**
     * @param string $firstName [optional] New first name value. Null by default.
     *
     * @return UserInterface
     */
    public function setFirstName($firstName = null);

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @param  string $lastName [optional] New last name value. Null by default.
     *
     * @return UserInterface
     */
    public function setLastName($lastName = null);

    /**
     * @return string
     */
    public function getLastName();

    /**
     * Set middle name
     *
     * @param string $middleName
     */
    public function setMiddleName($middleName);

    /**
     * @return string
     */
    public function getMiddleName();

    /**
     * Set name prefix
     *
     * @param string $namePrefix
     */
    public function setNamePrefix($namePrefix);

    /**
     * @return string
     */
    public function getNameSuffix();

    /**
     * @return string
     */
    public function getNamePrefix();

    /**
     * Set name suffix
     *
     * @param string $nameSuffix
     */
    public function setNameSuffix($nameSuffix);

    /**
     * Get full name with prefix, first, middle, last suffix names
     *
     * @return string
     */
    public function getFullName();

    /**
     * @param  string $image [optional] New image file name. Null by default.
     *
     * @return UserInterface
     */
    public function setImage($image = null);

    /**
     * @param  UploadedFile $imageFile
     *
     * @return UserInterface
     */
    public function setImageFile(UploadedFile $imageFile);

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
     *
     * @return UserInterface
     */
    public function setEnabled($enabled);

    /**
     * @param string $salt
     *
     * @return UserInterface
     */
    public function setSalt($salt);

    /**
     * @param  string $password New encoded password
     *
     * @return UserInterface
     */
    public function setPassword($password);

    /**
     * @param  string $password New password as plain string
     *
     * @return UserInterface
     */
    public function setPlainPassword($password);

    /**
     * Set confirmation token.
     *
     * @param  string $token
     *
     * @return UserInterface
     */
    public function setConfirmationToken($token);

    /**
     * @param  \DateTime $time [optional] New password request time. Null by default.
     *
     * @return UserInterface
     */
    public function setPasswordRequestedAt(\DateTime $time = null);

    /**
     * @param  \DateTime $time New login time
     *
     * @return UserInterface
     */
    public function setLastLogin(\DateTime $time);

    /**
     * @param  int $count New login count value
     *
     * @return UserInterface
     */
    public function setLoginCount($count);

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Returns the true Collection of Roles.
     *
     * @return Collection
     */
    public function getRolesCollection();

    /**
     * Pass a string, get the desired Role object or null
     *
     * @param  string $roleName Role name
     *
     * @return Role|null
     */
    public function getRole($roleName);

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
     *
     * @return boolean
     */
    public function hasRole($role);

    /**
     * Adds a Role to the Collection.
     *
     * @param  Role $role
     *
     * @return UserInterface
     */
    public function addRole(Role $role);

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
     *
     * @return UserInterface
     */
    public function setRoles($roles);

    /**
     * Directly set the Collection of Roles.
     *
     * @param  Collection $collection
     *
     * @throws \InvalidArgumentException
     *
     * @return UserInterface
     */
    public function setRolesCollection(Collection $collection);

    /**
     * Gets the groups granted to the user
     *
     * @return Collection
     */
    public function getGroups();

    /**
     * @return array
     */
    public function getGroupNames();

    /**
     * @param  string $name
     *
     * @return bool
     */
    public function hasGroup($name);

    /**
     * @param  GroupInterface $group
     *
     * @return UserInterface
     */
    public function addGroup(GroupInterface $group);

    /**
     * @param  GroupInterface $group
     *
     * @return UserInterface
     */
    public function removeGroup(GroupInterface $group);

    /**
     * @param GroupInterface[] $groups
     *
     * @return UserInterface
     */
    public function setGroups(array $groups);

    /**
     * Get groups ids
     *
     * @return array
     */
    public function getGroupsIds();

    /**
     * @return string|null
     */
    public function getImagePath();

    /**
     * Generate unique confirmation token
     *
     * @return string Token value
     */
    public function generateToken();

    /**
     * Pre persist event listener
     */
    public function beforeSave();

    /**
     * Invoked before the entity is updated.
     */
    public function preUpdate();

    /**
     * @return LocaleInterface
     */
    public function getCatalogLocale();

    /**
     * @param LocaleInterface $catalogLocale
     *
     * @return UserInterface
     */
    public function setCatalogLocale(LocaleInterface $catalogLocale);

    /**
     * @return LocaleInterface
     */
    public function getUiLocale();

    /**
     * @param LocaleInterface $uiLocale
     *
     * @return UserInterface
     */
    public function setUiLocale(LocaleInterface $uiLocale);

    /**
     * @return ChannelInterface
     */
    public function getCatalogScope();

    /**
     * @param ChannelInterface $catalogScope
     *
     * @return UserInterface
     */
    public function setCatalogScope(ChannelInterface $catalogScope);

    /**
     * @return CategoryInterface
     */
    public function getDefaultTree();

    /**
     * @param array $productGridFilters
     *
     * @return UserInterface
     */
    public function setProductGridFilters(array $productGridFilters = []);

    /**
     * @return array
     */
    public function getProductGridFilters();

    /**
     * @param CategoryInterface $defaultTree
     *
     * @return UserInterface
     */
    public function setDefaultTree(CategoryInterface $defaultTree);

    /**
     * @return bool
     */
    public function isEmailNotifications();
    /**
     * @param bool $emailNotifications
     *
     * @return UserInterface
     */
    public function setEmailNotifications($emailNotifications);

    /**
     * @param string the view alias
     *
     * @return DatagridView|null
     */
    public function getDefaultGridView($alias);

    /**
     * Get all default datagrid views
     *
     * @return DatagridView[]
     */
    public function getDefaultGridViews();

    /**
     * @param string            $alias
     * @param DatagridView|null $defaultGridView
     *
     * @return UserInterface
     */
    public function setDefaultGridView($alias, $defaultGridView);

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
