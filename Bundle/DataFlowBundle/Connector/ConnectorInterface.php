<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;

/**
 * Connector interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
