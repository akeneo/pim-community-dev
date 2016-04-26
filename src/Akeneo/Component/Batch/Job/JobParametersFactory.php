<?php

namespace Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Job\JobParameters\DefaultParametersRegistry;

/**
 * Allow to create immutable JobParameters with passed parameters completed by default values
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
     * @param JobInterface $job        the job we expect to configure
     * @param array        $parameters the parameters to use in addition of the default values
     *
     * @return JobParameters
     */
    public function create(JobInterface $job, array $parameters = [])
    {
        $defaults = $this->defaultRegistry->getDefaultParameters($job);
        $parameters = array_merge($defaults->getParameters(), $parameters);

        return new $this->jobParametersClass($parameters);
    }
}
