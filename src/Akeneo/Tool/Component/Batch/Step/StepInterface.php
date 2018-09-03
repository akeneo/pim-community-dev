<?php

namespace Akeneo\Tool\Component\Batch\Step;

use Akeneo\Tool\Component\Batch\Job\JobInterruptedException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;

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
 */
interface StepInterface
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
}
