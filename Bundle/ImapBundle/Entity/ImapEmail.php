<?php

namespace Oro\Bundle\ImapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EmailBundle\Entity\Email;

/**
 * IMAP Email
 *
 * @ORM\Table(name="oro_email_imap")
 * @ORM\Entity
 */
class ImapEmail
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="uid", type="integer")
     */
    protected $uid;

    /**
     * @var integer
     *
     * @ORM\Column(name="uid_validity", type="integer")
     */
    protected $uidValidity;

    /**
     * @var Email
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\EmailBundle\Entity\Email")
     * @ORM\JoinColumn(name="email_id", referencedColumnName="id", nullable=false)
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
     * Get email UID
     *
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set email UID
     *
     * @param int $uid
     * @return ImapEmail
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get email UIDVALIDITY
     *
     * @return int
     */
    public function getUidValidity()
    {
        return $this->uidValidity;
    }

    /**
     * Set email UIDVALIDITY
     *
     * @param int $uidValidity
     * @return ImapEmail
     */
    public function setUidValidity($uidValidity)
    {
        $this->uidValidity = $uidValidity;

        return $this;
    }

    /**
     * Get related email object
     *
     * @return Email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set related email object
     *
     * @param Email $email
     * @return ImapEmail
     */
    public function setEmail(Email $email)
    {
        $this->email = $email;

        return $this;
    }
}
