<?php

namespace Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Job\JobParameters\DefaultParametersRegistry;

/**
 * Allow to create immutable JobParameters with only default values or with default values and passed parameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobParametersFactory
{
    /** @var DefaultParametersRegistry */
    protected $defaultRegistry;

    /** @var string */
    protected $jobParametersClass;

    /**
     * @param DefaultParametersRegistry $registry
     * @param string                    $jobParametersClass
     */
    public function __construct(DefaultParametersRegistry $registry, $jobParametersClass)
    {
        $this->defaultRegistry = $registry;
        $this->jobParametersClass = $jobParametersClass;
    }

    /**
     * @param JobInterface $job
     *
     * @return JobParameters
     */
    public function createDefault(JobInterface $job)
    {
        $defaults = $this->defaultRegistry->getDefaultParameters($job);

        return new $this->jobParametersClass($defaults->getParameters());
    }

    /**
     * @param JobInterface $job
     * @param array        $parameters
     *
     * @return JobParameters
     */
    public function create(JobInterface $job, array $parameters)
    {
        $defaults = $this->defaultRegistry->getDefaultParameters($job);
        $parameters = array_merge($defaults->getParameters(), $parameters);

        return new $this->jobParametersClass($parameters);
    }
}
