<?php

namespace Pim\Bundle\UserBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\User\Model\GroupInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const GROUP_DEFAULT = 'All';
    const ROLE_ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';

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

    /** @var DateTime */
    protected $birthday;

    /**
     * Image filename
     *
     * @var string
     */
    protected $image;

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

    /** @var DateTime */
    protected $passwordRequestedAt;

    /** @var DateTime */
    protected $lastLogin;

    /** @var int */
    protected $loginCount = 0;

    /** @var Role[] */
    protected $roles;

    /** @var GroupInterface[] */
    protected $groups;

    /** @var string */
    protected $api;

    /** @var DateTime $createdAt */
    protected $createdAt;

    /** @var DateTime $updatedAt */
    protected $updatedAt;

    /** @var LocaleInterface */
    protected $catalogLocale;

    /** @var LocaleInterface */
    protected $uiLocale;

    /** @var Channel */
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

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->defaultGridViews = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
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
    public function unserialize($serialized)
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
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getClass()
    {
        return 'Pim\Bundle\UserBundle\Entity\UserInterface';
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
    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameSuffix()
    {
        return $this->nameSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullName()
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
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginCount()
    {
        return $this->loginCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->isEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstName($firstName = null)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastName($lastName = null)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * {@inheritdoc}
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function setNameSuffix($nameSuffix)
    {
        $this->nameSuffix = $nameSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function setBirthday(DateTime $birthday = null)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setImage($image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setImageFile(UploadedFile $imageFile)
    {
        $this->imageFile = $imageFile;
        // this will trienvogger PreUpdate callback even if only image has been changed
        $this->updatedAt = new DateTime('now', new \DateTimeZone('UTC'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetImageFile()
    {
        $this->imageFile = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean) $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfirmationToken($token)
    {
        $this->confirmationToken = $token;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPasswordRequestedAt(DateTime $time = null)
    {
        $this->passwordRequestedAt = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastLogin(DateTime $time)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLoginCount($count)
    {
        $this->loginCount = $count;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
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
    public function getRolesCollection()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole($roleName)
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
    public function hasRole($role)
    {
        if ($role instanceof Role) {
            $roleName = $role->getRole();
        } elseif (is_string($role)) {
            $roleName = $role;
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string'
            );
        }

        return (bool) $this->getRole($roleName);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(Role $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role)
    {
        if ($role instanceof Role) {
            $roleObject = $role;
        } elseif (is_string($role)) {
            $roleObject = $this->getRole($role);
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string'
            );
        }
        if ($roleObject) {
            $this->roles->removeElement($roleObject);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles($roles)
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
    public function setRolesCollection(Collection $collection)
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
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupNames()
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
    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(GroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupsIds()
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
    public function getImagePath()
    {
        if ($this->image) {
            return $this->getUploadDir(true) . '/' . $this->image;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generateToken()
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
    public function beforeSave()
    {
        $this->createdAt = new DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new DateTime('now', new \DateTimeZone('UTC'));
        $this->loginCount = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadDir($forWeb = false)
    {
        $ds = DIRECTORY_SEPARATOR;

        if ($forWeb) {
            $ds = '/';
        }

        $suffix = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m') : date('Y-m');

        return 'uploads'.$ds.'users'.$ds.$suffix;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogLocale()
    {
        return $this->catalogLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setCatalogLocale(LocaleInterface $catalogLocale)
    {
        $this->catalogLocale = $catalogLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUiLocale()
    {
        return $this->uiLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setUiLocale(LocaleInterface $uiLocale)
    {
        $this->uiLocale = $uiLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogScope()
    {
        return $this->catalogScope;
    }

    /**
     * {@inheritdoc}
     */
    public function setCatalogScope(ChannelInterface $catalogScope)
    {
        $this->catalogScope = $catalogScope;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTree()
    {
        return $this->defaultTree;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultTree(CategoryInterface $defaultTree)
    {
        $this->defaultTree = $defaultTree;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailNotifications()
    {
        return $this->emailNotifications;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailNotifications($emailNotifications)
    {
        $this->emailNotifications = $emailNotifications;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductGridFilters()
    {
        return $this->productGridFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductGridFilters(array $productGridFilters = [])
    {
        $this->productGridFilters = $productGridFilters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGridView($alias)
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
    public function getDefaultGridViews()
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
    public function setDefaultGridView($alias, $defaultGridView)
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
}
