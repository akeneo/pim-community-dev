<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Step;

use Pim\Bundle\BatchBundle\Step\AbstractStep;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Step used for test and always declared a stopped execution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InterruptedStep extends AbstractStep
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $execution)
    {
        $execution->setTerminateOnly();
    }
}
