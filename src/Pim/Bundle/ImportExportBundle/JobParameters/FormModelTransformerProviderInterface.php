<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\JobInstance;

/**
 * Define form model transformer to use to edit a JobParameters depending on the Job we want configure
 *
 * @author    Julien <juien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FormModelTransformerProviderInterface
{
    /**
     * @param JobInstance $jobInstance
     *
     * @return array
     */
    public function getFormModelTransformers(JobInstance $jobInstance);

    /**
     * @return boolean
     */
    public function supports(JobInterface $job);
}
