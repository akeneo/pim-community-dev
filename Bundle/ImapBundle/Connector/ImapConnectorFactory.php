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
     */
    public function __construct(ImapServicesFactory $factory, $connectorClass)
    {
        $this->factory = $factory;
        $this->connectorClass = $connectorClass;
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
