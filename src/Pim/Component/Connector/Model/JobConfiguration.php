<?php

namespace Pim\Component\Connector\Model;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Job configuration implementation
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobConfiguration implements JobConfigurationInterface
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
    public function __construct(JobExecution $jobExecution, $configuration)
    {
        $this->jobExecution  = $jobExecution;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobExecution()
    {
        return $this->jobExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function setJobExecution(JobExecution $jobExecution)
    {
        $this->jobExecution = $jobExecution;

        return $this;
    }
}
