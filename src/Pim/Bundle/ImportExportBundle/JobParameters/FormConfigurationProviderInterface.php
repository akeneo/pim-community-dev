<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\JobInstance;

/**
 * Define form options to use to edit a JobParameters depending on the Job we want configure
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FormConfigurationProviderInterface
{
    /**
     * @param JobInstance $jobInstance
     *
     * @return array
     */
    public function getFormConfiguration(JobInstance $jobInstance);

    /**
     * @return boolean
     */
    public function supports(JobInterface $job);
}
