<?php

namespace Akeneo\Tool\Component\Connector\Step;

use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * Simple task to be executed from a TaskletStep.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TaskletInterface extends StepExecutionAwareInterface
{
    /**
     * Execute the tasklet
     */
    public function execute();
}
