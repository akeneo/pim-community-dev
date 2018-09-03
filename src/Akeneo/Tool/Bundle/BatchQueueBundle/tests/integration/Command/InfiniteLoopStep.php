<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Command;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepInterface;

/**
 * Step that run infinitely for test purpose.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InfiniteLoopStep implements StepInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'infinite_loop_step';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StepExecution $stepExecution)
    {
        while (true) {
            sleep(1000000);
        }
    }
}
