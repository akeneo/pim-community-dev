<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Base archiver
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractArchiver implements ArchiverInterface
{
    /**
     * Get the relative archive path in the file system
     *
     * @return string
     */
    protected function getRelativeArchivePath(JobExecution $jobExecution)
    {
        $jobInstance = $jobExecution->getJobInstance();

        return sprintf(
            '%s/%s/%s/%s/%%filename%%',
            $jobInstance->getType(),
            $jobInstance->getAlias(),
            $jobExecution->getId(),
            $this->getName()
        );
    }
}
