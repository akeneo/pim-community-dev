<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type\JobParameters;

use Akeneo\Component\Batch\Job\Job;

/**
 * Define form options for a JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FormsOptionsInterface
{
    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return boolean
     */
    public function supports(Job $job);
}
