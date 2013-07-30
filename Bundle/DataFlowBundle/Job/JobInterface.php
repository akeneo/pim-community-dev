<?php
namespace Oro\Bundle\DataFlowBundle\Job;

use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;

/**
 * Job interface
 *
 */
interface JobInterface
{

    /**
     * Configure
     * @param ConfigurationInterface $connectorConfig the connector configuration
     * @param ConfigurationInterface $jobConfig       the job configuration
     *
     * @return JobInterface
     */
    public function configure(ConfigurationInterface $connectorConfig, ConfigurationInterface $jobConfig);

    /**
     * Get connector configuration
     *
     * @return ConfigurationInterface
     */
    public function getConnectorConfiguration();

    /**
     * Get connector configuration FQCN
     *
     * @return string
     */
    public function getConnectorConfigurationName();

    /**
     * Get configuration
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * Get configuration FQCN
     *
     * @return string
     */
    public function getConfigurationName();

    /**
     * Get messages
     * TODO: use configurable logger
     *
     * @return string
     */
    public function getMessages();

    /**
     * Run the job
     */
    public function run();
}
