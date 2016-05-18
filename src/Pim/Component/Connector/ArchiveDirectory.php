<?php

namespace Pim\Component\Connector;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use League\Flysystem\FilesystemInterface;

/**
 * The archive directory is where the import and exports files are stored.
 * It's represented by the container parameter "archive_dir" located in pim_parameters.yml.
 * By default it's "%kernel.root_dir%/archive".
 *
 * Before an import, the files are copied in the archive directory in order to be processed.
 * After an export, the files are copied from the archive directory to the export destination.
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ArchiveDirectory
{
    /**
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Ensure the archive directory exists and  returns it. It could be for example:
     *    - /home/akeneo/pim/app/archives/export/csv_family_export/14/output/
     *    - /home/akeneo/pim/app/archives/import/csv_family_import/15/input/
     *    - /home/akeneo/pim/app/archives/quick_export/csv_product_quick_export/16/output/
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     * @throws \Exception
     */
    public function getAbsolute(JobExecution $jobExecution)
    {
        $dir = $this->getRelative($jobExecution);
        if (false === $this->filesystem->has($dir)) {
            if (false === $this->filesystem->createDir($dir)) {
                throw new \Exception(sprintf('Impossible to create the archive directory "%s"', $dir));
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
