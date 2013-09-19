<?php

namespace Oro\Bundle\UserBundle\Entity;


use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\UserBundle\Entity\Status;
use Oro\Bundle\UserBundle\Entity\Email;
use Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\ImapBundle\Entity\ImapConfigurationOwnerInterface;
use Oro\Bundle\TagBundle\Entity\Tag;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use DateTime;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 * @ORM\Table(name="oro_user")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *      routeName="oro_user_index",
 *      defaultValues={
 *          "entity"={"icon"="icon-user", "label"="User", "plural_label"="Users"},
 *          "ownership"={
 *              "owner_type"="BUSINESS_UNIT",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="business_unit_owner_id"
 *          },
 *      }
 * )
 */
class User extends AbstractEntityFlexible implements
    AdvancedUserInterface,
    \Serializable,
    EntityUploadedImageInterface,
    Taggable,
    EmailOwnerInterface,
    ImapConfigurationOwnerInterface
{
    const ROLE_DEFAULT   = 'ROLE_USER';
    const ROLE_ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Oro\Versioned
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Oro\Versioned
     */
    protected $email;

    /**
     * First name
     *
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Oro\Versioned
     */
    protected $firstName;

    /**
     * Last name
     *
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Oro\Versioned
     */
    protected $lastName;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="birthday", type="datetime", nullable=true)
     * @Soap\ComplexType("date", nillable=true)
     * @Type("dateTime")
     * @Oro\Versioned
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
     * @Soap\ComplexType("boolean")
     * @Type("boolean")
     * @Oro\Versioned
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
     * @Soap\ComplexType("string", nillable=true)
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
     * @Soap\ComplexType("dateTime", nillable=true)
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
     * @var BusinessUnit
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @ORM\JoinColumn(name="business_unit_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $owner;

    /**
     * Set name formatting using "%first%" and "%last%" placeholders
     *
     * @var string
     *
     * @Exclude
     */
    protected $nameFormat;

    /**
     * @var Role[]
     *
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="oro_user_access_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Soap\ComplexType("int[]", nillable=true)
     * @Exclude
     * @Oro\Versioned("getLabel")
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
     * @Soap\ComplexType("int[]", nillable=true)
     * @Exclude
     * @Oro\Versioned("getName")
     */
    protected $groups;

    /**
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue[]
     *
     * @ORM\OneToMany(targetEntity="UserValue", mappedBy="entity", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $values;

    /**
     * @ORM\OneToOne(
     *  targetEntity="UserApi", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY"
     * )
     */
    protected $api;

    /**
     * @var Status[]
     *
     * @ORM\OneToMany(targetEntity="Status", mappedBy="user")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $statuses;

    /**
     * @var Status
     *
     * @ORM\OneToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    protected $currentStatus;

    /**
     * @var Email[]
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    protected $emails;

    /**
     * @var Tag[]
     *
     */
    protected $tags;

    /**
     * @var BusinessUnit[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\BusinessUnit", inversedBy="users")
     * @ORM\JoinTable(name="oro_user_business_unit",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="business_unit_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Exclude
     * @Oro\Versioned("getName")
     */
    protected $businessUnits;

    /**
     * @var ImapEmailOrigin
     *
     * @ORM\OneToOne(
     *     targetEntity="Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin", cascade={"all"}
     * )
     * @ORM\JoinColumn(name="imap_configuration_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @Exclude
     */
    protected $imapConfiguration;

    public function __construct()
    {
        parent::__construct();

        $this->salt            = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->roles           = new ArrayCollection();
        $this->groups          = new ArrayCollection();
        $this->statuses        = new ArrayCollection();
        $this->emails          = new ArrayCollection();
        $this->businessUnits   = new ArrayCollection();
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
            array(
                $this->password,
                $this->salt,
                $this->username,
                $this->enabled,
                $this->confirmationToken,
                $this->id,
            )
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
     * TODO: This is a temporary solution for get 'view' route in twig. Will be removed after EntityConfigBundle is finished
     * @return string
     */
    public function getClass()
    {
        return 'Oro\Bundle\UserBundle\Entity\User';
    }

    /**
     * Get name of field contains the primary email address
     *
     * @return string
     */
    public function getPrimaryEmailField()
    {
        return 'email';
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
    public function getFirstname()
    {
        return $this->firstName;
    }

    /**
     * Return last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastName;
    }

    /**
     * Return full name according to name format
     *
     * @see User::setNameFormat()
     * @param  string $format [optional]
     * @return string
     */
    public function getFullname($format = '')
    {
        return str_replace(
            array('%first%', '%last%'),
            array($this->getFirstname(), $this->getLastname()),
            $format ? $format : $this->getNameFormat()
        );
    }

    public function getName()
    {
        return $this->getFullname();
    }

    /**
     * Return birthday
     *
     * @return DateTime
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
     * @return DateTime
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
     * Get full name format. Defaults to "%first% %last%".
     *
     * @return string
     */
    public function getNameFormat()
    {
        return $this->nameFormat ?  $this->nameFormat : '%first% %last%';
    }

    /**
     * Get user created date/time
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get user last update date/time
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
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
     *
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
    public function setFirstname($firstName = null)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param  string $lastName [optional] New last name value. Null by default.
     * @return User
     */
    public function setLastname($lastName = null)
    {
        $this->lastName = $lastName;

        return $this;
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
        $this->updated = new DateTime('now', new \DateTimeZone('UTC')); // this will trigger PreUpdate callback even if only image has been changed

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
     * Set new format for a full name display. Use %first% and %last% placeholders, for example: "%last%, %first%".
     *
     * @param  string $format New format string
     * @return User
     */
    public function setNameFormat($format)
    {
        $this->nameFormat = $format;

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
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
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
        $names = array();

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
        $this->created = new DateTime('now', new \DateTimeZone('UTC'));
        $this->loginCount = 0;
    }

    /**
     * Invoked before the entity is updated.
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated = new DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Get User Statuses
     *
     * @return Status[]
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * Add Status to User
     *
     * @param  Status $status
     * @return User
     */
    public function addStatus(Status $status)
    {
        $this->statuses[] = $status;

        return $this;
    }

    /**
     * Get Current Status
     *
     * @return Status
     */
    public function getCurrentStatus()
    {
        return $this->currentStatus;
    }

    /**
     * Set User Current Status
     *
     * @param  Status $status
     * @return User
     */
    public function setCurrentStatus(Status $status = null)
    {
        $this->currentStatus = $status;

        return $this;
    }

    /**
     * Get User Emails
     *
     * @return Email[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Add Email to User
     *
     * @param  Email $email
     * @return User
     */
    public function addEmail(Email $email)
    {
        $this->emails[] = $email;

        $email->setUser($this);

        return $this;
    }

    /**
     * Delete Email from User
     *
     * @param  Email $email
     * @return User
     */
    public function removeEmail(Email $email)
    {
        $this->emails->removeElement($email);

        return $this;
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
     * {@inheritdoc}
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getBusinessUnits()
    {
        $this->businessUnits = $this->businessUnits ?: new ArrayCollection();

        return $this->businessUnits;
    }

    /**
     * @param ArrayCollection $businessUnits
     * @return User
     */
    public function setBusinessUnits($businessUnits)
    {
        $this->businessUnits = $businessUnits;

        return $this;
    }

    /**
     * @param  BusinessUnit $businessUnit
     * @return User
     */
    public function addBusinessUnit(BusinessUnit $businessUnit)
    {
        if (!$this->getBusinessUnits()->contains($businessUnit)) {
            $this->getBusinessUnits()->add($businessUnit);
        }

        return $this;
    }

    /**
     * @param  BusinessUnit $businessUnit
     * @return User
     */
    public function removeBusinessUnit(BusinessUnit $businessUnit)
    {
        if ($this->getBusinessUnits()->contains($businessUnit)) {
            $this->getBusinessUnits()->removeElement($businessUnit);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getImapConfiguration()
    {
        return $this->imapConfiguration;
    }

    /**
     * {@inheritDoc}
     */
    public function setImapConfiguration(ImapEmailOrigin $imapConfiguration)
    {
        $this->imapConfiguration = $imapConfiguration;

        return $this;
    }

    /**
     * @return BusinessUnit
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param BusinessUnit $owningBusinessUnit
     * @return User
     */
    public function setOwner($owningBusinessUnit)
    {
        $this->owner = $owningBusinessUnit;

        return $this;
    }
}
