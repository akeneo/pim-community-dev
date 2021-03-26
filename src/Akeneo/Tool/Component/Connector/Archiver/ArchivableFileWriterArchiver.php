<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchivableFileWriterArchiver extends AbstractFilesystemArchiver
{
    protected ZipFilesystemFactory $factory;
    private JobRegistry $jobRegistry;
    private FilesystemProvider $filesystemProvider;

    public function __construct(
        ZipFilesystemFactory $factory,
        FilesystemInterface $filesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider
    ) {
        $this->factory = $factory;
        $this->filesystem = $filesystem;
        $this->jobRegistry = $jobRegistry;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(JobExecution $jobExecution): void
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();
            if ($this->isWriterUsable($writer)) {
                $zipName = \sprintf('%s.zip', pathinfo($writer->getPath(), PATHINFO_FILENAME));

                $workingDirectory = $jobExecution->getExecutionContext()->get(
                    JobInterface::WORKING_DIRECTORY_PARAMETER
                );
                $localZipPath = $workingDirectory . DIRECTORY_SEPARATOR . $zipName;

                $localZipFilesystem = $this->factory->createZip(
                    $localZipPath
                );

                /** @var WrittenFileInfo $writtenFile */
                foreach ($writer->getWrittenFiles() as $writtenFile) {
                    if ($writtenFile->isLocalFile()) {
                        $stream = \fopen($writtenFile->sourceKey(), 'r');
                    } else {
                        $stream = $this->filesystemProvider->getFilesystem($writtenFile->sourceStorage())
                                                           ->readStream($writtenFile->sourceKey());
                    }
                    $localZipFilesystem->putStream($writtenFile->outputFilepath(), $stream);

                    if (\is_resource($stream)) {
                        \fclose($stream);
                    }
                }

                $localZipFilesystem->getAdapter()->getArchive()->close();

                $this->archiveZip($jobExecution, $localZipPath, $zipName);

                \unlink($localZipPath);
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
    public function getName(): string
    {
        return 'archive';
    }

    /**
     * Check if the job execution is supported
     *
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function supports(JobExecution $jobExecution): bool
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isWriterUsable($step->getWriter())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify if the writer is usable or not
     *
     * @param ItemWriterInterface $writer
     *
     * @return bool
     */
    protected function isWriterUsable(ItemWriterInterface $writer): bool
    {
        return $writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1;
    }
}
