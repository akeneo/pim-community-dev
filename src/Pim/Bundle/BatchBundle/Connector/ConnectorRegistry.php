<?php

namespace Pim\Bundle\BatchBundle\Connector;

use Pim\Bundle\BatchBundle\Job\JobInterface;
use Pim\Bundle\BatchBundle\Entity\Job;

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
     * TODO : Comments !!!
     */
    protected $importJobs = array();
    /**
     * TODO : Comments !!!
     */
    protected $exportJobs = array();

    /**
     * Add a job to a connector
     *
     * @param string       $connector the connector id
     * @param string       $type      the job type
     * @param string       $jobAlias  the job alias
     * @param JobInterface $job       the job
     *
     * @return ConnectorRegistry
     */
    public function addJobToConnector($connector, $type, $jobAlias, JobInterface $job)
    {
        if ($type === Job::TYPE_IMPORT) {
            $this->importJobs[$connector][$jobAlias] = $job;
        } else {
            $this->exportJobs[$connector][$jobAlias] = $job;
        }

        return $this;
    }

    /**
     * Get a registered job definition
     *
     * @param Pim\Bundle\BatchBundle\Entity\Job $job
     *
     * @return Pim\Bundle\BatchBundle\Job\JobInterface
	 */
    public function getJob(Job $job)
    {
        if ($connector = $this->getConnector($job->getConnector(), $job->getType())) {
            if ($jobDefinition = $this->getConnectorJob($connector, $job->getAlias())) {
                $jobDefinition->setConfiguration($job->getRawConfiguration());
                $job->setJobDefinition($jobDefinition);

                return $jobDefinition;
            }
        }
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

    private function getConnector($connector, $type)
    {
        switch ($type) {
            case Job::TYPE_IMPORT:
                return isset($this->importJobs[$connector]) ? $this->importJobs[$connector] : null;
            case Job::TYPE_EXPORT:
                return isset($this->exportJobs[$connector]) ? $this->exportJobs[$connector] : null;
        }
    }

    private function getConnectorJob($connector, $jobAlias)
    {
        return isset($connector[$jobAlias]) ? $connector[$jobAlias] : null;
    }
}
