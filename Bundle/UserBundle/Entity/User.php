<?php

namespace Oro\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use Oro\Bundle\LocaleBundle\Model\FullNameInterface;

use DateTime;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @ORM\Entity()
 * @ORM\Table(name="oro_user")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements
    AdvancedUserInterface,
    \Serializable,
    EntityUploadedImageInterface,
    FullNameInterface
{
    const ROLE_DEFAULT   = 'ROLE_USER';
    const GROUP_DEFAULT  = 'All';
    const ROLE_ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Type("string")
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Type("string")
     */
    protected $email;

    /**
     * Name prefix
     *
     * @var string
     *
     * @ORM\Column(name="name_prefix", type="string", length=255, nullable=true)
     * @Type("string")
     */
    protected $namePrefix;

    /**
     * First name
     *
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Type("string")
     */
    protected $firstName;

    /**
     * Middle name
     *
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=255, nullable=true)
     * @Type("string")
     */
    protected $middleName;

    /**
     * Last name
     *
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Type("string")
     */
    protected $lastName;

    /**
     * Name suffix
     *
     * @var string
     *
     * @ORM\Column(name="name_suffix", type="string", length=255, nullable=true)
     * @Type("string")
     */
    protected $nameSuffix;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @Type("date")
     */
    protected $birthday;

    /**
     * Image filename
     *
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     * @Exclude
     */
    protected $image;

    /**
     * Image filename
     *
     * @var UploadedFile
     *
     * @Exclude
     */
    protected $imageFile;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Type("boolean")
     */
    protected $enabled = true;

    /**
     * The salt to use for hashing
     *
     * @var string
     *
     * @ORM\Column(type="string")
     * @Exclude
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     * @Exclude
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     *
     * @Exclude
     */
    protected $plainPassword;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     * @Exclude
     */
    protected $confirmationToken;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="password_requested", type="datetime", nullable=true)
     * @Exclude
     */
    protected $passwordRequestedAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     * @Type("dateTime")
     */
    protected $lastLogin;

    /**
     * @var int
     *
     * @ORM\Column(name="login_count", type="integer", options={"default"=0, "unsigned"=true})
     * @Exclude
     */
    protected $loginCount;

    /**
     * @var Role[]
     *
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="oro_user_access_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Exclude
     */
    protected $roles;

    /**
     * @var Group[]
     *
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="oro_user_access_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Exclude
     */
    protected $groups;

    /**
     * @ORM\OneToOne(
     *  targetEntity="UserApi", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY"
     * )
     */
    protected $api;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->salt   = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->roles  = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    /**
     * Serializes the user.
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
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
     * Unserializes the user
     *
     * @param string $serialized
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
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * Get entity class name.
     * TODO: Remove this temporary solution for get 'view' route in twig after EntityConfigBundle is finished
     * @return string
     */
    public function getClass()
    {
        return 'Oro\Bundle\UserBundle\Entity\User';
    }

    /**
     * Returns the user unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Return last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Return middle name
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Return name prefix
     *
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * Return name suffix
     *
     * @return string
     */
    public function getNameSuffix()
    {
        return $this->nameSuffix;
    }

    /**
     * Return birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Return image filename
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Return image file
     *
     * @return UploadedFile
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritDoc}
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Gets login count number.
     *
     * @return int
     */
    public function getLoginCount()
    {
        return $this->loginCount;
    }

    /**
     * Get user created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get user last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return UserApi
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return $this->isEnabled();
    }

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param  string $username New username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param  string $email New email value
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param  string $firstName [optional] New first name value. Null by default.
     * @return User
     */
    public function setFirstName($firstName = null)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param  string $lastName [optional] New last name value. Null by default.
     * @return User
     */
    public function setLastName($lastName = null)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Set middle name
     *
     * @param string $middleName
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * Set name prefix
     *
     * @param string $namePrefix
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;
    }

    /**
     * Set name suffix
     *
     * @param string $nameSuffix
     */
    public function setNameSuffix($nameSuffix)
    {
        $this->nameSuffix = $nameSuffix;
    }

    /**
     *
     * @param  DateTime $birthday [optional] New birthday value. Null by default.
     * @return User
     */
    public function setBirthday(DateTime $birthday = null)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @param  string $image [optional] New image file name. Null by default.
     * @return User
     */
    public function setImage($image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @param  UploadedFile $imageFile
     * @return User
     */
    public function setImageFile(UploadedFile $imageFile)
    {
        $this->imageFile = $imageFile;
        // this will trigger PreUpdate callback even if only image has been changed
        $this->updatedAt = new DateTime('now', new \DateTimeZone('UTC'));

        return $this;
    }

    /**
     * Unset image file.
     *
     * @return User
     */
    public function unsetImageFile()
    {
        $this->imageFile = null;

        return $this;
    }

    /**
     * @param  bool $enabled User state
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean) $enabled;

        return $this;
    }

    /**
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param  string $password New encoded password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param  string $password New password as plain string
     * @return User
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Set confirmation token.
     *
     * @param  string $token
     * @return User
     */
    public function setConfirmationToken($token)
    {
        $this->confirmationToken = $token;

        return $this;
    }

    /**
     * @param  DateTime $time [optional] New password request time. Null by default.
     * @return User
     */
    public function setPasswordRequestedAt(DateTime $time = null)
    {
        $this->passwordRequestedAt = $time;

        return $this;
    }

    /**
     * @param  DateTime $time New login time
     * @return User
     */
    public function setLastLogin(DateTime $time)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * @param  int  $count New login count value
     * @return User
     */
    public function setLoginCount($count)
    {
        $this->loginCount = $count;

        return $this;
    }

    /**
     * @param  UserApi $api
     * @return User
     */
    public function setApi(UserApi $api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns the user roles merged with associated groups roles
     *
     * @return Role[] The array of roles
     */
    public function getRoles()
    {
        $roles = $this->roles->toArray();

        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles()->toArray());
        }

        return array_unique($roles);
    }

    /**
     * Returns the true Collection of Roles.
     *
     * @return Collection
     */
    public function getRolesCollection()
    {
        return $this->roles;
    }

    /**
     * Pass a string, get the desired Role object or null
     *
     * @param  string    $roleName Role name
     * @return Role|null
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
     * Never use this to check if this user has access to anything!
     * Use the AuthorizationChecker, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $authorizationChecker->isGranted('ROLE_USER');
     *
     * @param  Role|string               $role
     * @return boolean
     * @throws \InvalidArgumentException
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
     * Adds a Role to the Collection.
     *
     * @param  Role $role
     * @return User
     */
    public function addRole(Role $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Remove the Role object from collection
     *
     * @param  Role|string               $role
     * @throws \InvalidArgumentException
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
     * Pass an array or Collection of Role objects and re-set roles collection with new Roles.
     * Type hinted array due to interface.
     *
     * @param  array|Collection          $roles Array of Role objects
     * @return User
     * @throws \InvalidArgumentException
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
     * Directly set the Collection of Roles.
     *
     * @param  Collection                $collection
     * @return User
     * @throws \InvalidArgumentException
     */
    public function setRolesCollection($collection)
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
     * Gets the groups granted to the user
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
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
     * @param  string $name
     * @return bool
     */
    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    /**
     * @param  Group $group
     * @return User
     */
    public function addGroup(Group $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * @param  Group $group
     * @return User
     */
    public function removeGroup(Group $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImagePath()
    {
        if ($this->image) {
            return $this->getUploadDir(true) . '/' . $this->image;
        }

        return null;
    }

    /**
     * Generate unique confirmation token
     *
     * @return string Token value
     */
    public function generateToken()
    {
        return base_convert(bin2hex(hash('sha256', uniqid(mt_rand(), true), true)), 16, 36);
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new DateTime('now', new \DateTimeZone('UTC'));
        $this->loginCount = 0;
    }

    /**
     * Invoked before the entity is updated.
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Get the relative directory path to user avatar
     *
     * @param  bool   $forWeb
     * @return string
     */
    public function getUploadDir($forWeb = false)
    {
        $ds = DIRECTORY_SEPARATOR;

        if ($forWeb) {
            $ds = '/';
        }

        $suffix = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m') : date('Y-m');

        return 'uploads' . $ds . 'users' . $ds . $suffix;
    }

    /**
     * @var Pim\Bundle\CatalogBundle\Entity\Locale
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\Locale")
     */
    protected $catalogLocale;

    /**
     * @var Pim\Bundle\CatalogBundle\Entity\Channel
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\Channel")
     */
    protected $catalogScope;

    /**
     * @var Pim\Bundle\CatalogBundle\Model\CategoryInterface
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Model\CategoryInterface")
     */
    protected $defaultTree;

    /**
     * @return Locale
     */
    public function getCatalogLocale()
    {
        return $this->catalogLocale;
    }

    /**
     * @param Locale $catalogLocale
     *
     * @return User
     */
    public function setCatalogLocale($catalogLocale)
    {
        $this->catalogLocale = $catalogLocale;

        return $this;
    }

    /**
     * @return Channel
     */
    public function getCatalogScope()
    {
        return $this->catalogScope;
    }

    /**
     * @param Channel $catalogScope
     *
     * @return User
     */
    public function setCatalogScope($catalogScope)
    {
        $this->catalogScope = $catalogScope;

        return $this;
    }

    /**
     * @return CategoryInterface
     */
    public function getDefaultTree()
    {
        return $this->defaultTree;
    }

    /**
     * @param CategoryInterface $defaulTree
     *
     * @return User
     */
    public function setDefaultTree($defaultTree)
    {
        $this->defaultTree = $defaultTree;

        return $this;
    }
}
