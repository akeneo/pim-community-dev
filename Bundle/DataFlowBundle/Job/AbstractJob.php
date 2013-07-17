<?php
namespace Oro\Bundle\DataFlowBundle\Job;

use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;
use Oro\Bundle\DataFlowBundle\Exception\ConfigurationException;

/**
 * Abstract job
 *
 */
abstract class AbstractJob implements JobInterface
{

    /**
     * Connector configuration
     *
     * @var ConfigurationInterface
     */
    protected $connectorConfiguration;

    /**
     * Connector configuration FQCN
     * @var string
     */
    protected $connectorConfigurationName;

    /**
     * Job configuration
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * Job configuration FQCN
     * @var string
     */
    protected $configurationName;

    /**
     * @var \ArrayAccess
     */
    protected $messages;

    /**
     * Constructor
     *
     * @param string $configurationConnectorClassName the connector conf FQCN
     * @param string $configurationClassName          the job conf FQCN
     */
    public function __construct($configurationConnectorClassName, $configurationClassName)
    {
        $this->connectorConfigurationName = $configurationConnectorClassName;
        $this->configurationName          = $configurationClassName;
        $this->messages                   = array();
    }

    /**
     * {@inheritDoc}
     */
    public function configure(ConfigurationInterface $connectorConfig, ConfigurationInterface $jobConfig)
    {
        if (! $connectorConfig instanceof $this->connectorConfigurationName) {
            throw new ConfigurationException(
                'Connector configuration expected must be an instance of '.$this->connectorConfigurationName
            );
        }
        if (! $jobConfig instanceof $this->configurationName) {
            throw new ConfigurationException(
                'Job Configuration expected must be an instance of '.$this->configurationName
            );
        }
        $this->connectorConfiguration = $connectorConfig;
        $this->configuration          = $jobConfig;

        return $this;
    }

    /**
     * Run the job
     */
    public function run()
    {
        $this->extract();
        $this->transform();
        $this->load();
    }

    /**
     * Extract data
     */
    abstract protected function extract();

    /**
     * Transform data
     */
    abstract protected function transform();

    /**
     * Load data
     */
    abstract protected function load();

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

    /**
     * {@inheritDoc}
     */
    public function getConnectorConfiguration()
    {
        return $this->connectorConfiguration;
    }

    /**
     * {@inheritDoc}
     */
    public function getConnectorConfigurationName()
    {
        return $this->connectorConfigurationName;
    }

    /**
     * @return \ArrayAccess
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
