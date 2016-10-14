<?php

namespace Pim\Component\Connector\Archiver;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;

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

    /** @var JobRegistry */
    private $jobRegistry;

    /**
     * @param ZipFilesystemFactory $factory
     * @param Filesystem           $filesystem
     * @param JobRegistry          $jobRegistry
     */
    public function __construct(ZipFilesystemFactory $factory, Filesystem $filesystem, JobRegistry $jobRegistry)
    {
        $this->factory = $factory;
        $this->filesystem = $filesystem;
        $this->jobRegistry = $jobRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(JobExecution $jobExecution)
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
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
                    $stream = fopen($fullPath, 'r');
                    $filesystem->putStream($localPath, $stream);
                    fclose($stream);
                }
            }
        }
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
     * Verify if the writer is usable or not
     *
     * @param ItemWriterInterface $writer
     *
     * @return bool
     */
    protected function isWriterUsable(ItemWriterInterface $writer)
    {
        return $writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1;
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
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isWriterUsable($step->getWriter())) {
                return true;
            }
        }

        return false;
    }
}
