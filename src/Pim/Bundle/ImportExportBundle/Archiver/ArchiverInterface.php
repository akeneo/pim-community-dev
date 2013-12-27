<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Define an archiver
 *
 * @see \Pim\Bundle\ImportExportBundle\EventListener\JobExecutionArchivist
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ArchiverInterface
{
    /**
     * Archive a job execution
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution);

    /**
     * Get the archives of a job execution
     *
     * @return array
     */
    public function getArchives(JobExecution $JobExecution);

    /**
     * Get a specific archive of a job execution
     *
     * @param JobExecution $jobExecution
     * @param string $key
     *
     * @return \Gaufrette\Stream
     */
    public function getArchive(JobExecution $jobExecution, $key);

    /**
     * Get the archiver name
     *
     * @return string
     */
    public function getName();
}
