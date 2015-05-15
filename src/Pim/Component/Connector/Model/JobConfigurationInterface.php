<?php

namespace Pim\Component\Connector\Model;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

/**
 * A job configuration is used to transport raw configuration used
 * to launch BatchBundle jobs.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface JobConfigurationInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getConfiguration();

    /**
     * @param string $configuration
     *
     * @return JobConfigurationInterface
     */
    public function setConfiguration($configuration);

    /**
     * @return JobExecution
     */
    public function getJobExecution();

    /**
     * @param JobExecution $jobExecution
     *
     * @return JobConfigurationInterface
     */
    public function setJobExecution(JobExecution $jobExecution);
}
