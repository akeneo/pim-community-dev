<?php

namespace Oro\Bundle\ImapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EmailBundle\Entity\EmailOrigin;

/**
 * IMAP Email Origin
 *
 * @ORM\Entity
 */
class ImapEmailOrigin extends EmailOrigin
{
    /**
     * @var string
     *
     * @ORM\Column(name="imap_host", type="string", length=255, nullable=true)
     */
    protected $host;

    /**
     * @var string
     *
     * @ORM\Column(name="imap_port", type="integer", length=10, nullable=true)
     */
    protected $port;

    /**
     * The SSL type to be used to connect to IMAP server. Can be empty string, 'ssl' or 'tsl'
     *
     * @var string
     *
     * @ORM\Column(name="imap_ssl", type="string", length=3, nullable=true)
     */
    protected $ssl;

    /**
     * @var string
     *
     * @ORM\Column(name="imap_user", type="string", length=100, nullable=true)
     */
    protected $user;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     *
     * @ORM\Column(name="imap_password", type="string", length=100, nullable=true)
     */
    protected $password;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sync_code_updated", type="datetime", nullable=true)
     */
    protected $syncCodeUpdatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="synchronized", type="datetime", nullable=true)
     */
    protected $synchronizedAt;

    /**
     * @var int
     *
     * @ORM\Column(name="sync_code", type="integer", nullable=true)
     */
    protected $syncCode;

    /**
     * Gets the host name of IMAP server
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the host name of IMAP server
     *
     * @param string $host
     * @return ImapEmailOrigin
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Gets the port of IMAP server
     *
     * @return int
     */
    public function getPort()
    {
        return (int)$this->port;
    }

    /**
     * Sets the port of IMAP server
     *
     * @param int $port
     * @return ImapEmailOrigin
     */
    public function setPort($port)
    {
        $this->port = (int)$port;

        return $this;
    }

    /**
     * Gets the SSL type to be used to connect to IMAP server
     *
     * @return string
     */
    public function getSsl()
    {
        return $this->ssl;
    }

    /**
     * Sets the SSL type to be used to connect to IMAP server
     *
     * @param string $ssl Can be empty string, 'ssl' or 'tsl'
     * @return ImapEmailOrigin
     */
    public function setSsl($ssl)
    {
        $this->ssl = $ssl;

        return $this;
    }

    /**
     * Gets the user name
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the user name
     *
     * @param string $user
     * @return ImapEmailOrigin
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the encrypted password. Before use the password must be decrypted.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the password. The password must be encrypted.
     *
     * @param  string $password New encrypted password
     * @return ImapEmailOrigin
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get date/time when this object was changed
     *
     * @return \DateTime
     */
    public function getSyncCodeUpdatedAt()
    {
        return $this->syncCodeUpdatedAt;
    }

    /**
     * Get date/time when emails from this origin were synchronized
     *
     * @return \DateTime
     */
    public function getSynchronizedAt()
    {
        return $this->synchronizedAt;
    }

    /**
     * Set date/time when emails from this origin were synchronized
     *
     * @param \DateTime $synchronizedAt
     * @return ImapEmailOrigin
     */
    public function setSynchronizedAt($synchronizedAt)
    {
        $this->synchronizedAt = $synchronizedAt;

        return $this;
    }

    /**
     * Get the last synchronization result code
     *
     * @return int
     */
    public function getSyncCode()
    {
        return $this->syncCode;
    }

    /**
     * Set the last synchronization result code
     *
     * @param int $syncCode
     * @return ImapEmailOrigin
     */
    public function setSyncCode($syncCode)
    {
        $this->syncCode = $syncCode;

        return $this;
    }
}
