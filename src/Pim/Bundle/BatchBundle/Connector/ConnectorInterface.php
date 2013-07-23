<?php

namespace Pim\Bundle\BatchBundle\Connector;

use Pim\Bundle\BatchBundle\Configuration\ConfigurationInterface;

/**
 * Connector interface
 */
interface ConnectorInterface
{

    /**
     * Configure
     * @param ConfigurationInterface $configuration
     *
     * @return ConnectorInterface
     */
    public function configure(ConfigurationInterface $configuration);

    /**
     * Get configuration
     * @return ConfigurationInterface
     */
    public function getConfiguration();
}
