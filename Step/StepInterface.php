<?php

namespace Akeneo\Bundle\BatchBundle\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Batch domain interface representing the configuration of a step. As with the
 * Job, a Step is meant to explicitly represent the configuration of a step by
 * a developer, but also the ability to execute the step.
 *
 * Inspired by Spring Batch org.springframework.batch.core.Step;
 *
 */
interface StepInterface
{
    /**
     * @return The name of this step
     */
    public function getName();

    /**
     * Process the step and assign progress and status meta information to the
     * StepExecution provided. The Step is responsible for setting the meta
     * information and also saving it if required by the implementation.
     *
     * @param StepExecution $stepExecution an entity representing the step to be executed
     *
     * @throws JobInterruptedException if the step is interrupted externally
     */
    public function execute(StepExecution $stepExecution);

    /**
     * Provide the configuration of the step
     *
     * @return array
     */
    public function getConfiguration();

    /**
     * Set the configuration for the step
     *
     * @param array $config
     */
    public function setConfiguration(array $config);

    /**
     * Get the configurable step elements
     *
     * @return array
     */
    public function getConfigurableStepElements();
}
