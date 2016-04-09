<?php

namespace Akeneo\Component\Batch\Step;

use Akeneo\Component\Batch\Job\JobInterruptedException;
use Akeneo\Component\Batch\Model\ConfigurableInterface;
use Akeneo\Component\Batch\Model\StepExecution;

/**
 * Batch domain interface representing the configuration of a step. As with the
 * Job, a Step is meant to explicitly represent the configuration of a step by
 * a developer, but also the ability to execute the step.
 *
 * Inspired by Spring Batch org.springframework.batch.core.Step;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * TODO: this interface should not enforce step configuration, not the same concern!
 */
interface StepInterface extends ConfigurableInterface
{
    /**
     * @return string The name of this step
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
     *
     * @deprecated will be removed in 1.7, please use ConfigurableInterface::configure
     */
    public function setConfiguration(array $config);

    /**
     * Get the configurable step elements
     *
     * @return array
     */
    public function getConfigurableStepElements();
}
