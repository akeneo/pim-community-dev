<?php

namespace Pim\Component\Connector;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use League\Flysystem\FilesystemInterface;

/**
 * The archive storage is where the import and exports files are stored.
 * It's represented by the container parameter "archive_dir" located in pim_parameters.yml.
 * By default it's "%kernel.root_dir%/archive".
 *
 * Before an import, the files are copied in the archive directory in order to be processed.
 * After an export, the files are copied from the archive directory to the export destination.
 *
 * File are named according to the job code that is launched, without any extension. It can be for example
 * "csv_family_export" or "xlsx_family_import".
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ArchiveStorage
{
    /** @var FilesystemInterface */
    private $filesystem;

    /**
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Get the pathname of the file for a Job Execution. It could be for example:
     *    - /home/akeneo/pim/app/archives/export/csv_family_export/14/output/csv_family_export (an output export file)
     *    - /home/akeneo/pim/app/archives/import/csv_family_import/15/input/csv_family_import (an input export file)
     *    - /home/akeneo/pim/app/archives/quick_export/csv_product_quick_export/16/output/csv_product_quick_export
     *      (an output quick export file)
     *
     * This pathname does not mean the file already exists when you get it. It's just the location where the file
     * should be at the end of the import/export.
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     * @throws \LogicException
     */
    public function getPathname(JobExecution $jobExecution)
    {
        return $this->getAbsoluteDirectory($jobExecution) . $jobExecution->getJobInstance()->getCode();
    }

    /**
     * Get the archive directory, and ensure its existence. It could be for example:
     *    - /home/akeneo/pim/app/archives/export/csv_family_export/14/output/
     *    - /home/akeneo/pim/app/archives/import/csv_family_import/15/input/
     *    - /home/akeneo/pim/app/archives/quick_export/csv_product_quick_export/16/output/
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     * @throws \LogicException
     */
    public function getAbsoluteDirectory(JobExecution $jobExecution)
    {
        $dir = $this->getRelative($jobExecution);
        if (false === $this->filesystem->has($dir)) {
            if (false === $this->filesystem->createDir($dir)) {
                throw new \LogicException(sprintf('Impossible to create the archive directory "%s"', $dir));
            }
        }

        return $this->getPathPrefix() . $this->getRelative($jobExecution);
    }

    /**
     * Returns the internal directory where will be stored the files. It could be for example:
     *    - export/csv_family_export/14/output/
     *    - import/csv_family_import/15/input/
     *    - quick_export/csv_product_quick_export/16/output/
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    protected function getRelative(JobExecution $jobExecution)
    {
        $jobInstance = $jobExecution->getJobInstance();

        return
            $jobInstance->getType() . DIRECTORY_SEPARATOR .
            $jobInstance->getAlias() . DIRECTORY_SEPARATOR .
            $jobExecution->getId() . DIRECTORY_SEPARATOR .
            $this->getSubDirectoryAccordingToJobType($jobInstance) . DIRECTORY_SEPARATOR;
    }

    /**
     * The aim of this method is to be backward compatible with the previous archiving system.
     *
     * @param JobInstance $jobInstance
     *
     * @return string
     */
    protected function getSubDirectoryAccordingToJobType(JobInstance $jobInstance)
    {
        if ('import' === $jobInstance->getType()) {
            return 'input';
        }

        if (in_array($jobInstance->getType(), ['export', 'quick_export'])) {
            return 'output';
        }

        return $jobInstance->getType();
    }

    /**
     * @return string
     */
    private function getPathPrefix()
    {
        // TODO: we should not get the path prefix like that
        // TODO: it should be injected in the constructor
        return $this->filesystem->getAdapter()->getPathPrefix();
    }
}
