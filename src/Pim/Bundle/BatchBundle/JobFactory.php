<?php

namespace Pim\Bundle\BatchBundle;

use Pim\Bundle\BatchBundle\Model\Job;

/**
 * A job instance factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobFactory
{
    public function createJob($code)
    {
        $job = new Job();
        $job->setCode($code);

        return $job;
    }
}
