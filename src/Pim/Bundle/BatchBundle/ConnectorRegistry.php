<?php

namespace Pim\Bundle\BatchBundle;

use Pim\Bundle\BatchBundle\JobFactory;
use Pim\Bundle\BatchBundle\StepFactory;
use Pim\Bundle\BatchBundle\Entity\JobInstance;

class ConnectorRegistry
{
    protected $jobs = array();
    protected $jobFactory;
    protected $stepFactory;

    public function __construct(JobFactory $jobFactory, StepFactory $stepFactory)
    {
        $this->jobFactory = $jobFactory;
        $this->stepFactory = $stepFactory;
    }

    public function getJob(JobInstance $job)
    {
        if ($connector = $this->getConnector($job->getConnector(), $job->getType())) {
            if ($jobDefinition = $this->getConnectorJob($connector, $job->getAlias())) {
                $jobDefinition->setConfiguration($job->getRawConfiguration());
                $job->setJobDefinition($jobDefinition);

                return $jobDefinition;
            }
        }
    }

    public function getJobs($type)
    {
        return $this->jobs[$type];
    }

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

        $this->jobs[$jobType][$jobConnector][$jobAlias]->addStep(
            $this->stepFactory->createStep($stepTitle, $stepReader, $stepProcessor, $stepWriter)
        );
    }

    private function getConnector($connector, $type)
    {
        return isset($this->jobs[$type][$connector]) ? $this->jobs[$type][$connector] : null;
    }

    private function getConnectorJob($connector, $jobAlias)
    {
        return isset($connector[$jobAlias]) ? $connector[$jobAlias] : null;
    }

    public function getConnectors()
    {
        return array_keys($this->jobs);
    }
}
