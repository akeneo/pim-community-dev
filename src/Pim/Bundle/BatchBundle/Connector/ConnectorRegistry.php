<?php

namespace Pim\Bundle\BatchBundle\Connector;

use Pim\Bundle\BatchBundle\Job\JobInterface;
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

    protected $importJobs = array();
    protected $exportJobs = array();

    /**
     * Add a job to a connector
     *
     * @param string             $connectorId the connector id
     * @param string             $jobId       the job id
     * @param JobInterface       $job         the job
     *
     * @return ConnectorRegistry
     */
    public function addJobToConnector($connector, $type, $jobAlias, JobInterface $job)
    {
        if ($type === AbstractJob::TYPE_IMPORT) {
            $this->importJobs[$connector][$jobAlias] = $job;
        } else {
               $this->exportJobs[$connector][$jobAlias] = $job;
        }

        return $this;
    }

    public function getJob($connector, $type, $jobAlias)
    {
        return $this->jobs[$connector][$jobAlias];
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
}
