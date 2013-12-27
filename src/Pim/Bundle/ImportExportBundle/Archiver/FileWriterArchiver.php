<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\ImportExportBundle\Writer\File\FileWriter;
use Pim\Bundle\ImportExportBundle\Writer\File\ArchivableWriterInterface;
use Oro\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Filesystem;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriterArchiver extends AbstractArchiver
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
            $writer = $step->getWriter();

            if ($writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1) {
                continue;
            }

            if ($writer instanceof FileWriter) {
                $key = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    array(
                        '%filename%' => basename($writer->getPath()),
                    )
                );
                $this->filesystem->write($key, file_get_contents($writer->getPath()), true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getArchives(JobExecution $jobExecution)
    {
        $archives = array();
        $keys = $this->filesystem->listKeys(dirname($this->getRelativeArchivePath($jobExecution)));
        foreach ($keys['keys'] as $key) {
            $archives[] = $this->filesystem->createStream($key);
        }

        return $archives;
    }

    public function getName()
    {
        return 'output';
    }
}
