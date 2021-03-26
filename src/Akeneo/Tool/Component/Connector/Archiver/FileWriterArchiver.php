<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

/**
 * Archive files written by job execution to provide them through a download button
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriterArchiver extends AbstractFilesystemArchiver
{
    protected JobRegistry $jobRegistry;
    private FilesystemProvider $filesystemProvider;

    public function __construct(Filesystem $filesystem, JobRegistry $jobRegistry, FilesystemProvider $fsProvider)
    {
        $this->filesystem = $filesystem;
        $this->jobRegistry = $jobRegistry;
        $this->filesystemProvider = $fsProvider;
    }

    /**
     * Archive files used by job execution (input / output)
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution): void
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();

            if ($this->isUsableWriter($writer)) {
                $this->doArchive($jobExecution, $writer->getWrittenFiles());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'output';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution): bool
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isUsableWriter($step->getWriter())) {
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
    protected function isUsableWriter(ItemWriterInterface $writer): bool
    {
        return $writer instanceof ArchivableWriterInterface && 0 < count($writer->getWrittenFiles());
    }

    /**
     * @param JobExecution $jobExecution
     * @param WrittenFileInfo[] $filesToArchive
     */
    protected function doArchive(JobExecution $jobExecution, array $filesToArchive): void
    {
        foreach ($filesToArchive as $fileToArchive) {
            $archivedFilePath = strtr(
                $this->getRelativeArchivePath($jobExecution),
                [
                    '%filename%' => $fileToArchive->outputFilepath(),
                ]
            );

            try {
                if ($fileToArchive->isLocalFile()) {
                    $this->filesystem->write($archivedFilePath, $fileToArchive->sourceKey());
                } else {
                    $sourceFilesystem = $this->filesystemProvider->getFilesystem($fileToArchive->sourceStorage());
                    $this->filesystem->writeStream(
                        $archivedFilePath,
                        $sourceFilesystem->readStream($fileToArchive->sourceKey())
                    );
                }
            } catch (FileNotFoundException $e) {
                // TODO: log?
                continue;
            }
        }
    }
}
