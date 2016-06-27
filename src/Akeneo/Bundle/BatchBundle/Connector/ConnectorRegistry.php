<?php

namespace Akeneo\Bundle\BatchBundle\Connector;

use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobFactory;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Step\StepFactory;

/**
 * Aims to register all connectors
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ConnectorRegistry
{
    /** @var array */
    protected $jobs = [];

    /** @var JobFactory */
    protected $jobFactory;

    /** @var StepFactory */
    protected $stepFactory;

    /**
     * @param JobFactory  $jobFactory
     * @param StepFactory $stepFactory
     */
    public function __construct(JobFactory $jobFactory, StepFactory $stepFactory)
    {
        $this->jobFactory = $jobFactory;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Get a registered job definition from a JobInstance
     *
     * @param JobInstance $jobInstance
     *
     * @throws \LogicException
     *
     * @return JobInterface
     */
    public function getJob(JobInstance $jobInstance)
    {
        if ($connector = $this->getConnector($jobInstance->getConnector(), $jobInstance->getType())) {
            if ($job = $this->getConnectorJob($connector, $jobInstance->getJobName())) {
                return $job;
            }
        }

        return null;
    }

    /**
     * Get the list of jobs
     *
     * @param string $type
     *
     * @return JobInterface[]
     */
    public function getJobs($type)
    {
        return $this->jobs[$type];
    }

    /**
     * Add a step to an existing job (or create it)
     *
     * @param string $jobConnector
     * @param string $jobType
     * @param string $jobName
     * @param string $stepName
     * @param string $stepClass
     * @param array  $services
     * @param array  $parameters
     *
     * @return null
     */
    public function addStepToJob(
        $jobConnector,
        $jobType,
        $jobName,
        $stepName,
        $stepClass,
        array $services,
        array $parameters
    ) {
        if (!isset($this->jobs[$jobType][$jobConnector][$jobName])) {
            $this->jobs[$jobType][$jobConnector][$jobName] = $this->jobFactory->createJob($jobName);
        }

        /** @var Job $job */
        $job = $this->jobs[$jobType][$jobConnector][$jobName];

        $step = $this->stepFactory->createStep($stepName, $stepClass, $services, $parameters);
        $job->addStep($stepName, $step);
    }

    /**
     * @param string $connector
     * @param string $type
     *
     * @return mixed
     *
     * TODO: Return mixed.. string or null?
     */
    public function getConnector($connector, $type)
    {
        return isset($this->jobs[$type][$connector]) ? $this->jobs[$type][$connector] : null;
    }

    /**
     * Get list of connectors
     *
     * @param string $jobType
     *
     * @return array
     */
    public function getConnectors($jobType = null)
    {
        if ($jobType !== null) {
            if (isset($this->jobs[$jobType])) {
                return array_keys($this->jobs[$jobType]);
            }

            return array();
        }

        return array_unique(array_keys($this->jobs));
    }

    /**
     * @param array  $connector
     * @param string $jobName
     *
     * @return JobInterface|null
     */
    private function getConnectorJob($connector, $jobName)
    {
        return isset($connector[$jobName]) ? $connector[$jobName] : null;
    }
}
