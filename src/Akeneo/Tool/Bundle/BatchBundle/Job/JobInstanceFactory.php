<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Job;

use Akeneo\Tool\Component\Batch\Model\JobInstance;

/**
 * Job instance factory
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobInstanceFactory
{
    /** @var string */
    protected $jobInstanceClass;

    /**
     * @param string $jobInstanceClass
     */
    public function __construct($jobInstanceClass)
    {
        $this->jobInstanceClass = $jobInstanceClass;
    }

    /**
     * Create a job instance
     *
     * @param string $type
     *
     * @return JobInstance
     */
    public function createJobInstance($type = null)
    {
        return new $this->jobInstanceClass(null, $type);
    }
}
