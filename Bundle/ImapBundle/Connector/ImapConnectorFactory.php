<?php

namespace Oro\Bundle\ImapBundle\Connector;

/**
 * Provides a factory class that creates ImapConnector objects.
 */
class ImapConnectorFactory
{
    /**
     * @var ImapServicesFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $connectorClass;

    /**
     * @param ImapServicesFactory $factory The factory responsible to create objects of ImapServices class
     * @param string $connectorClass The full name of class implements the IMAP connector
     *
     * TODO: Also we need to pass the configuration service to the constructor.
     *       To be implemented in the one of the future sprints
     */
    public function __construct(ImapServicesFactory $factory, $connectorClass)
    {
        $this->factory = $factory;
        $this->connectorClass = $connectorClass;
    }

    /**
     * Creates the IMAP connector for the given user
     *
     * @param int $userId The user id
     * @return ImapConnector
     */
    public function createUserImapConnector($userId)
    {
        // TODO: Implement logic to get IMAP configuration for the given user
        $host = '';
        $port = '';
        $ssl = '';
        $user = '';
        $password = '';

        return $this->createImapConnector(
            new ImapConfig($host, $port, $ssl, $user, $password)
        );
    }

    /**
     * Creates the IMAP connector based on the given configuration
     *
     * @param ImapConfig $config The configuration of IMAP service, such as host, port, user name and others
     * @return ImapConnector
     */
    public function createImapConnector(ImapConfig $config)
    {
        $connectorClass = $this->connectorClass;

        return new $connectorClass($config, $this->factory);
    }
}
