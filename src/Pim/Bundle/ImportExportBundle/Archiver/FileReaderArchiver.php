<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\ImportExportBundle\Reader\File\CsvReader;
use Pim\Bundle\ImportExportBundle\Writer\File\FileWriter;
use Pim\Bundle\ImportExportBundle\Writer\File\ArchivableWriterInterface;
use Oro\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Filesystem;
use Pim\Bundle\ImportExportBundle\Reader\File\FileReader;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiver implements ArchiverInterface
{
    /** @var Filesystem */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
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
            $reader = $step->getReader();

            if ($reader instanceof FileReader) {
                $key = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    array(
                        '%directory%' => 'input',
                        '%filename%'  => pathinfo($reader->getFilePath(), PATHINFO_FILENAME),
                    )
                );
                $this->filesystem->write($key, file_get_contents($reader->getFilePath()), true);
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
            '%s/%s/%s/input/%%filename%%.csv',
            $jobInstance->getType(),
            $jobInstance->getAlias(),
            $jobInstance->getId()
        );
    }
}
