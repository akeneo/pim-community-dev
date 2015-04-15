<?php

namespace Pim\Bundle\EnrichBundle\Factory;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration;

/**
 * Job configuration factory
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditConfigurationFactory
{
    /** @var string */
    protected $massEditJobConfClass;

    /**
     * @param string $massEditJobConfClass
     */
    function __construct($massEditJobConfClass)
    {
        $this->massEditJobConfClass = $massEditJobConfClass;
    }

    /**
     * @param JobExecution $jobExecution
     * @param string       $configuration
     *
     * @return MassEditJobConfiguration
     */
    public function create(JobExecution $jobExecution, $configuration)
    {
        return new $this->massEditJobConfClass($jobExecution, $configuration);
    }
}
