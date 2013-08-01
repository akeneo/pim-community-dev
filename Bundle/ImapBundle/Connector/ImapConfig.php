<?php

namespace Oro\Bundle\ImapBundle\Connector;

class ImapConfig
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $ssl;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @param string $host The host name of IMAP server
     * @param string $port The port of IMAP server
     * @param string $ssl The SSL type to be used to connect to IMAP server. Can be empty string, 'ssl' or 'tsl'
     * @param string $user The user name
     * @param string $password The user password
     */
    public function __construct($host = null, $port = null, $ssl = null, $user = null, $password = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->ssl = $ssl;
        $this->user = $user;
        $this->password = $password;
    }

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
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Gets the port of IMAP server
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the port of IMAP server
     *
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
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
     */
    public function setSsl($ssl)
    {
        $this->ssl = $ssl;
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
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Gets the user password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the user password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
