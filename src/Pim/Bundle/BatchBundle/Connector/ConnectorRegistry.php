<?php

namespace Pim\Bundle\BatchBundle\Connector;

use Pim\Bundle\BatchBundle\Job\JobInterface;
use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\BatchBundle\Job\JobFactory;
use Pim\Bundle\BatchBundle\Step\StepFactory;

/**
 * Aims to register all connectors
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
     * @param string                 $stepTitle
     * @param ItemReaderInterface    $stepReader
     * @param ItemProcessorInterface $stepProcessor
     * @param ItemWriterInterface    $stepWriter
     *
     * @return null
     */
    public function addStepToJob($jobConnector, $jobType, $jobAlias, $jobTitle, $stepTitle, $stepReader, $stepProcessor, $stepWriter)
    {
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
}
