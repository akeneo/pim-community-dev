<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\tests\Integration\Job;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DumbStep implements StepInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'dumb_step';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StepExecution $stepExecution)
    {
    }
}
