<?php

namespace Pim\Component\Connector\Factory;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Job configuration factory
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobConfigurationFactory
{
    /** @var string */
    protected $jobConfigClass;

    /**
     * @param string $jobConfigClass
     */
    public function __construct($jobConfigClass)
    {
        $this->jobConfigClass = $jobConfigClass;
    }

    /**
     * @param JobExecution $jobExecution
     * @param string       $configuration
     *
     * @return \Pim\Component\Connector\Model\JobConfigurationInterface
     */
    public function create(JobExecution $jobExecution, $configuration)
    {
        return new $this->jobConfigClass($jobExecution, $configuration);
    }
}
