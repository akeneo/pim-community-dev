<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * Email Address
 *
 * @ORM\Table(name="oro_email_address",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="oro_email_address_uq", columns={"email"})},
 *      indexes={@ORM\Index(name="oro_email_address_idx", columns={"email"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class EmailAddress
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Type("integer")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     * @Type("dateTime")
     */
    protected $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime")
     * @Type("dateTime")
     */
    protected $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Type("string")
     */
    protected $email;

    // TODO: This should be replaces by array or proxy class. Need an investigation how to do this. Also see related code in EmailAddressManager class
    // @codingStandardsIgnoreStart
    /**
     * @var EmailOwnerInterface
     * @Exclude
     */
    private $_owner1;
    /**
     * @var EmailOwnerInterface
     * @Exclude
     */
    private $_owner2;
    // @codingStandardsIgnoreEnd

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get entity created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get entity updated date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * Get email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email address.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email owner
     *
     * @return EmailOwnerInterface
     */
    public function getOwner()
    {
        $owner = $this->_owner1;
        if ($owner === null) {
            $owner = $this->_owner2;
        }

        return $owner;
    }

    /**
     * Set email owner
     *
     * @param EmailOwnerInterface|null $owner
     * @return $this
     */
    public function setOwner(EmailOwnerInterface $owner = null)
    {
        if ($owner instanceof User) {
            $this->_owner1 = $owner;
            $this->_owner2 = null;
        } elseif ($owner instanceof Contact) {
            $this->_owner1 = null;
            $this->_owner2 = $owner;
        } else {
            $this->_owner1 = null;
            $this->_owner2 = null;
        }

        return $this;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event listener
     *
     * @ORM\PreUpdate
     */
    public function beforeUpdate()
    {
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
