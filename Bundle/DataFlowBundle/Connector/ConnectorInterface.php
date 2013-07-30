<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;

/**
 * Connector interface
 *
 *
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
