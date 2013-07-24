<?php

namespace Pim\Bundle\BatchBundle\Connector;

use Pim\Bundle\BatchBundle\Configuration\ConfigurationInterface;
use Pim\Bundle\BatchBundle\Job\JobInterface;
use Pim\Bundle\BatchBundle\Exception\ConfigurationException;

/**
 * Abstract connector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractConnector implements ConnectorInterface
{
    /**
     * Connector configuration
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * Connector configuration FQCN
     * @var string
     */
    protected $configurationName;

    /**
     * Constructor
     *
     * @param string $configurationClassName
     */
    public function __construct($configurationClassName)
    {
        $this->configurationName = $configurationClassName;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(ConfigurationInterface $configuration)
    {
        if (! $configuration instanceof $this->configurationName) {
            throw new ConfigurationException(
                'Configuration expected must be an instance of '.$this->configurationName
            );
        }
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationName()
    {
        return $this->configurationName;
    }
}
