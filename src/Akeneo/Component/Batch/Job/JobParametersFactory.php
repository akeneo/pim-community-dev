<?php

namespace Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderRegistry;

/**
 * Allow to create immutable JobParameters with passed parameters completed by default values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @api
 */
class JobParametersFactory
{
    /** @var DefaultValuesProviderRegistry */
    protected $defaultRegistry;

    /** @var string */
    protected $jobParametersClass;

    /**
     * @param DefaultValuesProviderRegistry $registry
     * @param string                        $jobParametersClass
     */
    public function __construct(DefaultValuesProviderRegistry $registry, $jobParametersClass)
    {
        $this->defaultRegistry = $registry;
        $this->jobParametersClass = $jobParametersClass;
    }

    /**
     * @param JobInterface $job        the job we expect to configure
     * @param array        $parameters the parameters to use in addition of the default values
     *
     * @return JobParameters
     *
     * @api
     */
    public function create(JobInterface $job, array $parameters = [])
    {
        $provider = $this->defaultRegistry->get($job);
        $parameters = array_merge($provider->getDefaultValues(), $parameters);

        return new $this->jobParametersClass($parameters);
    }
}
