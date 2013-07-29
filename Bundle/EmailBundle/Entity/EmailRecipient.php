<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Email Recipient
 *
 * @ORM\Table(name="oro_email_recipient")
 * @ORM\Entity
 */
class EmailRecipient
{
    const TO = 'to';
    const CC = 'cc';
    const BCC = 'bcc';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=3)
     */
    protected $type;

    /**
     * @var EmailAddress
     *
     * @ORM\ManyToOne(targetEntity="EmailAddress", fetch="EAGER")
     * @ORM\JoinColumn(name="email_address_id", referencedColumnName="id", nullable=false)
     */
    protected $emailAddress;

    /**
     * @var EmailOrigin
     *
     * @ORM\ManyToOne(targetEntity="Email", inversedBy="recipients")
     * @ORM\JoinColumn(name="email_id", referencedColumnName="id")
     */
    protected $email;

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
     * Get full email name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set full email name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get recipient type.
     *
     * @return string Can be 'to', 'cc' or 'bcc'
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set recipient type
     *
     * @param string $type Can be 'to', 'cc' or 'bcc'
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get email address
     *
     * @return EmailAddress
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Set email address
     *
     * @param EmailAddress $emailAddress
     * @return $this
     */
    public function setEmailAddress(EmailAddress $emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Get email
     *
     * @return Email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param Email $email
     * @return $this
     */
    public function setEmail(Email $email)
    {
        $this->email = $email;

        return $this;
    }
}
