<?php
namespace Pim\Bundle\BatchBundle\Tests\Unit\Job\Demo;

use Pim\Bundle\BatchBundle\Job\AbstractJob;

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
