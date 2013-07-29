<?php

namespace Pim\Bundle\BatchBundle\Job;

/**
 * Dummy class for job parameter.
 * TODO Implement job parameter: in fact, it's jobConfiguration
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.SimpleJob
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobParameters
{
    public function __toString()
    {
        return "<dummy parameters>";
    }
}
