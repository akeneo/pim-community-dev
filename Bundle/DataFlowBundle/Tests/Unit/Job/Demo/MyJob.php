<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Job\Demo;

use Oro\Bundle\DataFlowBundle\Job\AbstractJob;

/**
 * Demo job
 *
 *
 */
class MyJob extends AbstractJob
{
    /**
     * {@inheritDoc}
     */
    public function extract()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function transform()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function load()
    {
        return true;
    }
}
