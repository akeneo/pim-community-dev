<?php

declare(strict_types=1);

namespace Akeneo\Component\Batch\Queue;

use Akeneo\Component\Batch\Model\JobExecutionMessage;

/**
 * This class aims to publish job execution message into a queue.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface JobExecutionQueueInterface
{
    /**
     * @param JobExecutionMessage $jobExecutionMessage
     */
    public function publish(JobExecutionMessage $jobExecutionMessage) : void;
}
