<?php

namespace Pim\Component\Connector\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use Pim\Component\Connector\Step\TaskletInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TaskletStep extends AbstractStep
{
    /** @var array */
    protected $configuration;

    /** @var TaskletInterface */
    protected $tasklet;

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->tasklet->setStepExecution($stepExecution);
        $this->tasklet->execute($this->configuration);
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
    public function setConfiguration(array $config)
    {
        $this->configuration = $config;

        return $this;
    }

    /**
     * @return TaskletInterface
     */
    public function getTasklet()
    {
        return $this->tasklet;
    }

    /**
     * @param TaskletInterface $tasklet
     */
    public function setTasklet($tasklet)
    {
        $this->tasklet = $tasklet;
    }
}
