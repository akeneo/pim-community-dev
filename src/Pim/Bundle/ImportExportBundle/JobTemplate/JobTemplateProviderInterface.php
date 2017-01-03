<?php

namespace Pim\Bundle\ImportExportBundle\JobTemplate;

use Akeneo\Component\Batch\Model\JobInstance;

/**
 * A job template provider is capable of retrieving templates code that have been registered or generates default ones
 * based on a given job instance.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface JobTemplateProviderInterface
{
    /**
     * Generates the create template code for the given job instance
     *
     * @param JobInstance $jobInstance
     *
     * @return string
     */
    public function getCreateTemplate(JobInstance $jobInstance);

    /**
     * Generates the show template code for the given job instance
     *
     * @param JobInstance $jobInstance
     *
     * @return string
     */
    public function getShowTemplate(JobInstance $jobInstance);

    /**
     * Generates the edit template code for the given job instance
     *
     * @param JobInstance $jobInstance
     *
     * @return string
     */
    public function getEditTemplate(JobInstance $jobInstance);
}
