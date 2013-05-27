<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Job\Demo;

use Oro\Bundle\DataFlowBundle\Job\AbstractJob;

/**
 * Demo job
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
