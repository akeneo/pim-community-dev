<?php

namespace Pim\Bundle\EnrichBundle\Entity;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Mass edit job configuration
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditJobConfiguration
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $configuration;

    /** @var JobExecution */
    protected $jobExecution;

    /**
     * @param JobExecution $jobExecution
     * @param string       $configuration
     */
    public function __construct($jobExecution, $configuration)
    {
        $this->jobExecution  = $jobExecution;
        $this->configuration = $configuration;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return MassEditJobConfiguration
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     *
     * @return MassEditJobConfiguration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return JobExecution
     */
    public function getJobExecution()
    {
        return $this->jobExecution;
    }

    /**
     * @param JobExecution $jobExecution
     *
     * @return MassEditJobConfiguration
     */
    public function setJobExecution(JobExecution $jobExecution)
    {
        $this->jobExecution = $jobExecution;

        return $this;
    }
}
