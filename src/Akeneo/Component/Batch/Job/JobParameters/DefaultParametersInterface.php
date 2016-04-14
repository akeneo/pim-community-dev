<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\Job;

/**
 * Default parameters to be used to create a JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DefaultParametersInterface
{
    /**
     * @return array
     */
    public function getParameters();

    /**
     * @return boolean
     */
    public function supports(Job $job);
}
