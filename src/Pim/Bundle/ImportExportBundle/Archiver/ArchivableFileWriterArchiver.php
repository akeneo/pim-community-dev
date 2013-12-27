<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Gaufrette\Filesystem;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Step\ItemStep;
use Pim\Bundle\ImportExportBundle\Writer\File\FileWriter;
use Pim\Bundle\ImportExportBundle\Writer\File\ArchivableWriterInterface;
use Pim\Bundle\ImportExportBundle\Filesystem\ZipFilesystemFactory;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchivableFileWriterArchiver extends AbstractArchiver
{
    /** @var ZipFilesystemFactory */
    protected $factory;

    /** @var string */
    protected $directory;

    /** @var Filesystem */
    protected $filesystem;

    /**
     * @param ZipFilesystemFactory $factory
     * @param string               $directory
     */
    public function __construct(ZipFilesystemFactory $factory, $directory, Filesystem $filesystem)
    {
        $this->factory    = $factory;
        $this->directory  = $directory;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(JobExecution $jobExecution)
    {
        foreach ($jobExecution->getJobInstance()->getJob()->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();
            if ($writer instanceof FileWriter &&
                $writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1) {
                    $filesystem = $this->getZipFilesystem(
                        $jobExecution,
                        sprintf('%s.zip', pathinfo($writer->getPath(), PATHINFO_FILENAME))
                    );

                foreach ($writer->getWrittenFiles() as $fullPath => $localPath) {
                    $filesystem->write($localPath, file_get_contents($fullPath), true);
                }
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'archive';
    }

    protected function getZipFilesystem(JobExecution $jobExecution, $zipName)
    {
        return $this->factory->createZip(
            sprintf(
                '%s/%s',
                $this->directory,
                strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    array('%filename%' => $zipName)
                )
            )
        );
    }
}
