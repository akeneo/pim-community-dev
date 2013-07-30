<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;
use Oro\Bundle\DataFlowBundle\Job\JobInterface;
use Oro\Bundle\DataFlowBundle\Exception\ConfigurationException;

/**
 * Abstract connector
 *
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
