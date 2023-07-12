<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use Psr\Log\LoggerInterface;

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
    private LoggerInterface $logger;

    public function __construct(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $fsProvider,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->jobRegistry = $jobRegistry;
        $this->filesystemProvider = $fsProvider;
        $this->logger = $logger;
    }

    /**
     * Archive files used by job execution (input / output)
     *
     * @param StepExecution $stepExecution
     */
    public function archive(StepExecution $stepExecution): void
    {
        $jobExecution = $stepExecution->getJobExecution();
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep || $step->getName() !== $stepExecution->getStepName()) {
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
    public function supports(StepExecution $stepExecution): bool
    {
        $jobExecution = $stepExecution->getJobExecution();
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
        return $writer instanceof ArchivableWriterInterface;
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
                    $stream = \fopen($fileToArchive->sourceKey(), 'r');
                } else {
                    $stream = $this->filesystemProvider->getFilesystem($fileToArchive->sourceStorage())->readStream(
                        $fileToArchive->sourceKey()
                    );
                }
                if ($stream) {
                    $this->filesystem->writeStream($archivedFilePath, $stream);
                }
                if (\is_resource($stream)) {
                    \fclose($stream);
                }
            } catch (UnableToReadFile $e) {
                $this->logger->warning(
                    'The remote file could not be read from the remote filesystem',
                    [
                        'key' => $fileToArchive->sourceKey(),
                        'storage' => $fileToArchive->sourceStorage(),
                        'exception' => [
                            'type' => \get_class($e),
                            'message' => $e->getMessage(),
                        ],
                    ]
                );
            }
        }
    }
}
