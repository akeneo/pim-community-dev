<?php

namespace Pim\Bundle\BaseConnectorBundle\Step;

use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Simple task to be executed from a TaskletStep.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
interface TaskletInterface extends StepExecutionAwareInterface
{
    /**
     * Execute the tasklet
     *
     * @param array $configuration
     */
    public function execute(array $configuration);
}
