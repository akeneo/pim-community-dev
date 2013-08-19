<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * Import controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportController extends JobController
{
    /**
     * {@inheritdoc}
     */
    protected function getJobType()
    {
        return Job::TYPE_IMPORT;
    }
}
