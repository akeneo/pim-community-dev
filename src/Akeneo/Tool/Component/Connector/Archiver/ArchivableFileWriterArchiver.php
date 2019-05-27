<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use League\Flysystem\Filesystem;

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
                $zipName = sprintf('%s.zip', pathinfo($writer->getPath(), PATHINFO_FILENAME));

                $workingDirectory = $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER);
                $localZipPath = $workingDirectory.DIRECTORY_SEPARATOR.$zipName;

                $localZipFilesystem = $this->factory->createZip(
                    $localZipPath
                );

                foreach ($writer->getWrittenFiles() as $fullPath => $localPath) {
                    $stream = fopen($fullPath, 'r');
                    $localZipFilesystem->putStream($localPath, $stream);

                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                $localZipFilesystem->getAdapter()->getArchive()->close();

                $this->archiveZip($jobExecution, $localZipPath, $zipName);

                unlink($localZipPath);
            }
        }
    }

    /**
     * Put the generated zip file to the archive destination location
     */
    protected function archiveZip(JobExecution $jobExecution, string $localZipPath, string $destName)
    {
        $destPath = strtr(
            $this->getRelativeArchivePath($jobExecution),
            ['%filename%' => $destName]
        );

        if (!$this->filesystem->has(dirname($destPath))) {
            $this->filesystem->createDir(dirname($destPath));
        }

        $zipArchive = fopen($localZipPath, 'r');
        $this->filesystem->writeStream($destPath, $zipArchive);

        if (is_resource($zipArchive)) {
            fclose($zipArchive);
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
