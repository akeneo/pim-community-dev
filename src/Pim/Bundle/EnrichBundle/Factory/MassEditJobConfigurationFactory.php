<?php

namespace Pim\Bundle\EnrichBundle\Factory;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BaseConnectorBundle\Model\JobConfiguration;

/**
 * Job configuration factory
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditJobConfigurationFactory
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
     * @return \Pim\Bundle\BaseConnectorBundle\Model\JobConfiguration
     */
    public function create(JobExecution $jobExecution, $configuration)
    {
        return new $this->jobConfigClass($jobExecution, $configuration);
    }
}
