<?php

namespace Pim\Bundle\BatchBundle\Connector;

use Pim\Bundle\BatchBundle\Configuration\ConfigurationInterface;

/**
 * Connector interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
