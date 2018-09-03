<?php

namespace Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;

/**
 * Define the default values for parameters that need to be be used to create a JobParameters depending of the Job we
 * need to configure. For instance, define that a filepath parameter is fulfilled with '/tmp/myfile.csv' by default.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DefaultValuesProviderInterface
{
    /**
     * @return array
     */
    public function getDefaultValues();

    /**
     * @return boolean
     */
    public function supports(JobInterface $job);
}
