<?php

namespace Pim\Bundle\BatchBundle\Connector;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\BatchBundle\Job\JobInterface;
use Pim\Bundle\BatchBundle\Connector\ConnectorInterface;
use Pim\Bundle\BatchBundle\Job\AbstractJob;

/**
 * Aims to register all connectors
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectorRegistry
{
    /**
     * Connectors references
     * @var \ArrayAccess
     */
    protected $connectors;

    /**
     * Jobs references
     * @var \ArrayAccess
     */
    protected $jobs;

    /**
     * Jobs references
     * @var \ArrayAccess
     */
    protected $importJobs;

    /**
     * Jobs references
     * @var \ArrayAccess
     */
    protected $exportJobs;

    /**
     * Connector to jobs aliases
     * @var \ArrayAccess
     */
    protected $connectorToJobs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->connectors      = array();
        $this->jobs            = array();
        $this->connectorToJobs = array();
        $this->importJobs      = array();
        $this->exportJobs      = array();
    }

    /**
     * Add a job to a connector
     *
     * @param string             $connectorId the connector id
     * @param ConnectorInterface $connector   the connector
     * @param string             $jobId       the job id
     * @param JobInterface       $job         the job
     * @param string             $type        the type of job
     *
     * @return ConnectorRegistry
     */
    public function addJobToConnector($connectorId, ConnectorInterface $connector, $jobId, JobInterface $job, $type)
    {
        $this->connectors[$connectorId] = $connector;

        $this->jobs[$jobId] = $job;

        if (!isset($this->connectorToJobs[$connectorId])) {
            $this->connectorToJobs[$connectorId] = array();
        }

        $this->connectorToJobs[$connectorId][] = $jobId;

        if ($type === AbstractJob::TYPE_IMPORT) {
            $this->importJobs[]= $jobId;
        } else {
            $this->exportJobs[]= $jobId;
        }

        return $this;
    }

    /**
     * Get the list of connectors
     *
     * @return multitype:ConnectorInterface
     */
    public function getConnectors()
    {
        return $this->connectors;
    }

    /**
     * Get the list of jobs
     *
     * @return multitype:JobInterface
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Get the list of jobs
     *
     * @return multitype:JobInterface
     */
    public function getExportJobs()
    {
        return $this->exportJobs;
    }

    /**
     * Get the list of jobs
     *
     * @return multitype:JobInterface
     */
    public function getImportJobs()
    {
        return $this->importJobs;
    }

    /**
     * Get the associative array of connectors aliases to jobs aliases
     *
     * @return multitype
     */
    public function getConnectorToJobs()
    {
        return $this->connectorToJobs;
    }
}
