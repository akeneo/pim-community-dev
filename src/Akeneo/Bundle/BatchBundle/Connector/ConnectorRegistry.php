<?php

namespace Akeneo\Bundle\BatchBundle\Connector;

use Akeneo\Bundle\BatchBundle\Job\JobFactory;
use Akeneo\Bundle\BatchBundle\Step\StepFactory;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\JobInstance;

/**
 * Aims to register all connectors
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
     *
     * @throws \LogicException
     * @return JobInterface
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
     * @param string $jobConnector
     * @param string $jobType
     * @param string $jobAlias
     * @param string $jobTitle
     * @param string $stepTitle
     * @param string $stepClass
     * @param array  $services
     * @param array  $parameters
     *
     * @return null
     */
    public function addStepToJob(
        $jobConnector,
        $jobType,
        $jobAlias,
        $jobTitle,
        $stepTitle,
        $stepClass,
        array $services,
        array $parameters
    ) {
        if (!isset($this->jobs[$jobType][$jobConnector][$jobAlias])) {
            $this->jobs[$jobType][$jobConnector][$jobAlias] = $this->jobFactory->createJob($jobTitle);
        }

        /** @var Job $job */
        $job = $this->jobs[$jobType][$jobConnector][$jobAlias];

        $step = $this->stepFactory->createStep($stepTitle, $stepClass, $services, $parameters);
        $job->addStep($stepTitle, $step);
    }

    /**
     * Set job show template
     *
     * @param string $jobConnector The connector
     * @param string $jobType      The job type
     * @param string $jobAlias     The job alias
     * @param string $template     Reference to the template (format: bundle:section:template.format.engine)
     */
    public function setJobShowTemplate($jobConnector, $jobType, $jobAlias, $template)
    {
        if (!isset($this->jobs[$jobType][$jobConnector][$jobAlias])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Job %s - %s - %s is not defined',
                    $jobConnector,
                    $jobType,
                    $jobAlias
                )
            );
        }

        $job = $this->jobs[$jobType][$jobConnector][$jobAlias];
        $job->setShowTemplate($template);
    }

    /**
     * Set job edit template
     *
     * @param string $jobConnector The connector
     * @param string $jobType      The job type
     * @param string $jobAlias     The job alias
     * @param string $template     Reference to the template (format: bundle:section:template.format.engine)
     */
    public function setJobEditTemplate($jobConnector, $jobType, $jobAlias, $template)
    {
        if (!isset($this->jobs[$jobType][$jobConnector][$jobAlias])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Job %s - %s - %s is not defined',
                    $jobConnector,
                    $jobType,
                    $jobAlias
                )
            );
        }

        $job = $this->jobs[$jobType][$jobConnector][$jobAlias];
        $job->setEditTemplate($template);
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
     * @param string $jobAlias
     *
     * @return Job|null
     */
    private function getConnectorJob($connector, $jobAlias)
    {
        return isset($connector[$jobAlias]) ? $connector[$jobAlias] : null;
    }
}
