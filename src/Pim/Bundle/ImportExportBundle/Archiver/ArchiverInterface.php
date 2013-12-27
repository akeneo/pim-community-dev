<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ArchiverInterface
{
    public function archive(JobExecution $jobExecution);
    public function getArchives(JobExecution $JobExecution);
    public function getName();
}
