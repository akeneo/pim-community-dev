<?php

namespace Oro\Bundle\BatchBundle\Connector;

use Oro\Bundle\BatchBundle\Job\JobInterface;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Job\JobFactory;
use Oro\Bundle\BatchBundle\Step\StepFactory;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Job\Job;

/**
 * Aims to register all connectors
 *
 */
class ConnectorRegistry
{
    protected $jobs = array();
    protected $jobFactory;
    protected $stepFactory;

    /**
     * Constructor
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
     * @return JobInterface
     * @throws \LogicException
     */
    public function getJob(JobInstance $jobInstance)
    {
        if ($connector = $this->getConnector($jobInstance->getConnector(), $jobInstance->getType())) {
            if ($job = $this->getConnectorJob($connector, $jobInstance->getAlias())) {
                $job->setConfiguration($jobInstance->getRawConfiguration());
                $jobInstance->setJob($job);

                return $job;
            }
        }

        return null;
    }

    /**
     * Get the list of jobs
     * @param string $type
     *
     * @return JobInterface[]
     *
     * TODO : Rather return an array of array of JobInterface ?
     */
    public function getJobs($type)
    {
        return $this->jobs[$type];
    }

    /**
     * Add a step to an existig job (or create it)
     *
     * @param string                 $jobConnector
     * @param string                 $jobType
     * @param string                 $jobAlias
     * @param string                 $jobTitle
     * @param string                 $stepName
     * @param string                 $stepTitle
     * @param ItemReaderInterface    $stepReader
     * @param ItemProcessorInterface $stepProcessor
     * @param ItemWriterInterface    $stepWriter
     * @return null
     */
    public function addStepToJob(
        $jobConnector,
        $jobType,
        $jobAlias,
        $jobTitle,
        $stepTitle,
        $stepReader,
        $stepProcessor,
        $stepWriter
    ) {
        if (!isset($this->jobs[$jobType][$jobConnector][$jobAlias])) {
            $this->jobs[$jobType][$jobConnector][$jobAlias] = $this->jobFactory->createJob($jobTitle);
        }

        /** @var Job $job */
        $job = $this->jobs[$jobType][$jobConnector][$jobAlias];

        $step = $this->stepFactory->createStep($stepTitle, $stepReader, $stepProcessor, $stepWriter);
        $job->addStep($stepTitle, $step);
    }

    /**
     * @param string $connector
     * @param string $type
     *
     * @return mixed
     * TODO : Return mixed.. string or null ?
     */
    public function getConnector($connector, $type)
    {
        return isset($this->jobs[$type][$connector]) ? $this->jobs[$type][$connector] : null;
    }

    /**
     * @param array  $connector
     * @param string $jobAlias
     *
     * @return mixed
     * TODO : Return mixed.. string or null ?
     */
    private function getConnectorJob($connector, $jobAlias)
    {
        return isset($connector[$jobAlias]) ? $connector[$jobAlias] : null;
    }

    /**
     * Get list of connectors
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
}
