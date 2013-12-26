<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Gaufrette\Filesystem;
use Gaufrette\Adapter;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Step\ItemStep;
use Pim\Bundle\ImportExportBundle\Reader\File\CsvReader;
use Pim\Bundle\ImportExportBundle\Writer\File\FileWriter;
use Pim\Bundle\ImportExportBundle\Writer\File\ArchivableWriterInterface;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchivableFileWriterArchiver implements ArchiverInterface
{
    /** @var Filesystem */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Archive files used by job execution (input / output)
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution)
    {

        foreach ($jobExecution->getJobInstance()->getJob()->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();
            if ($writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1) {
                $filesystem = $this->filesystem;
                if (!$filesystem) {
                    $zipAdapter = new Adapter\Zip(
                        strtr(
                            $this->getRelativeArchivePath($jobExecution),
                            array(
                                '%filename%' => sprintf('%s.zip', pathinfo($writer->getPath(), PATHINFO_FILENAME)),
                            )
                        )
                    );
                    $filesystem = new Filesystem($zipAdapter);
                }

                foreach ($writer->getWrittenFiles() as $fullPath => $localPath) {
                    $filesystem->write($localPath, file_get_contents($fullPath), true);
                }
            }
        }
    }

    /**
     * Get the relative archive path in the file system
     *
     * @return string
     */
    protected function getRelativeArchivePath(JobExecution $jobExecution)
    {
        $jobInstance = $jobExecution->getJobInstance();

        return sprintf(
            '%s/%s/%s/output/%%filename%%',
            $jobInstance->getType(),
            $jobInstance->getAlias(),
            $jobInstance->getId()
        );
    }
}
