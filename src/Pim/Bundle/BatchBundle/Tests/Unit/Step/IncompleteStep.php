<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Step;

use Pim\Bundle\BatchBundle\Step\AbstractStep;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Step used for test and always declared a incomplete execution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class IncompleteStep extends AbstractStep
{
    /**
     * {@inheritDoc}
     */
    public function getConfiguration()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfiguration(array $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $execution)
    {
        $execution->setStatus(new BatchStatus(BatchStatus::FAILED));
    }
}
