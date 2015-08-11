<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\Filesystem\ZipFilesystemFactory;
use Pim\Bundle\BaseConnectorBundle\Writer\File\ArchivableWriterInterface;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchivableFileWriterArchiver extends AbstractFilesystemArchiver
{
    /** @var ZipFilesystemFactory */
    protected $factory;

    /** @var string */
    protected $directory;

    /**
     * @param ZipFilesystemFactory $factory
     * @param Filesystem           $filesystem
     */
    public function __construct(ZipFilesystemFactory $factory, Filesystem $filesystem)
    {
        $this->factory    = $factory;
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
            if ($this->isWriterUsable($writer)) {
                $filesystem = $this->getZipFilesystem(
                    $jobExecution,
                    sprintf('%s.zip', pathinfo($writer->getPath(), PATHINFO_FILENAME))
                );

                foreach ($writer->getWrittenFiles() as $fullPath => $localPath) {
                    $filesystem->put($localPath, file_get_contents($fullPath));
                }
            }
        }
    }

    /**
     * Verify if the writer is usable or not
     *
     * @param ItemWriterInterface $writer
     *
     * @return bool
     */
    protected function isWriterUsable(ItemWriterInterface $writer)
    {
        return $writer instanceof FileWriter &&
            $writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'archive';
    }

    /**
     * Get a fresh zip filesystem for the given job execution
     *
     * @param JobExecution $jobExecution
     * @param string       $zipName
     *
     * @return Filesystem
     */
    protected function getZipFilesystem(JobExecution $jobExecution, $zipName)
    {
        $zipPath = strtr(
            $this->getRelativeArchivePath($jobExecution),
            ['%filename%' => $zipName]
        );

        if (!$this->filesystem->has(dirname($zipPath))) {
            $this->filesystem->createDir(dirname($zipPath));
        }

        return $this->factory->createZip(
            $this->filesystem->getAdapter()->getPathPrefix() .
            $zipPath
        );
    }

    /**
     * Check if the job execution is supported
     *
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function supports(JobExecution $jobExecution)
    {
        foreach ($jobExecution->getJobInstance()->getJob()->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isWriterUsable($step->getWriter())) {
                return true;
            }
        }

        return false;
    }
}
