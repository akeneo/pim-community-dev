<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File;

use Akeneo\Platform\TailoredExport\Domain\FileToExport;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Box\Spout\Writer\WriterInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface,
    ArchivableWriterInterface
{
    private const DATETIME_FORMAT = 'Y-m-d_H-i-s';

    private Filesystem $localFileSystem;
    private FileWriterFactory $fileWriterFactory;
    private ?StepExecution $stepExecution = null;

    /** @var WrittenFileInfo[] */
    private array $writtenFiles = [];
    private int $numberOfWrittenLines = 0;
    private ?string $openedPath = null;
    private ?WriterInterface $writer = null;

    public function __construct(Filesystem $localFileSystem, FileWriterFactory $fileWriterFactory)
    {
        $this->localFileSystem = $localFileSystem;
        $this->fileWriterFactory = $fileWriterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->numberOfWrittenLines = 0;
        $this->writtenFiles = [];
        $this->openedPath = null;
        $this->writer = null;

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFileSystem->mkdir($exportDirectory);
        }
    }

    /**
     * {@inheritdoc}
     * @param array<array> $items
     */
    public function write(array $items): void
    {
        $this->openedPath = $this->getPath();
        if (!empty($items) && $this->numberOfWrittenLines === 0) {
            $this->writer = $this->fileWriterFactory->build();
            $this->writer->openToFile($this->openedPath);
            $this->addHeadersIfNeeded(current($items));
        }

        foreach ($items as $item) {
            if ($this->isMaxLinesPerFileReached()) {
                $this->writer->close();
                $this->writtenFiles[] = WrittenFileInfo::fromLocalFile($this->openedPath, basename($this->openedPath));

                $this->writer = $this->fileWriterFactory->build();
                $this->openedPath = $this->getPath();
                $this->writer->openToFile($this->openedPath);
                $this->addHeadersIfNeeded($item['mapped_product']);
            }

            $this->writer->addRow($item['mapped_product']);
            $this->writeMedia($item['files_to_write']);
            $this->numberOfWrittenLines++;
        }

        $this->stepExecution->incrementSummaryInfo('write', count($items));
    }

    /**
     * Flush items into a file
     */
    public function flush(): void
    {
        if ($this->numberOfWrittenLines !== 0 && $this->openedPath !== null) {
            $this->writtenFiles[] = WrittenFileInfo::fromLocalFile($this->openedPath, basename($this->openedPath));
        }

        if (null !== $this->writer) {
            $this->writer->close();
        }
    }

    /**
     * Get the file path in which to write the data
     */
    public function getPath(): string
    {
        $parameters = $this->getStepExecution()->getJobParameters();
        $jobExecution = $this->getStepExecution()->getJobExecution();
        $filePath = $parameters->get('filePath');

        if (!str_contains($filePath, '%')) {
            return $filePath;
        }

        $jobLabel = '';
        $datetime = $this->getStepExecution()->getStartTime()->format(self::DATETIME_FORMAT);
        if (null !== $jobExecution->getJobInstance()) {
            $jobLabel = preg_replace('#[^A-Za-z0-9\.]#', '_', $jobExecution->getJobInstance()->getLabel());
        }

        $filePath = strtr($filePath, ['%datetime%' => $datetime, '%job_label%' => $jobLabel]);
        if ($this->areSeveralFilesNeeded()) {
            $fileNumber = floor($this->numberOfWrittenLines / $this->getMaxLinesPerFile()) + 1;
            $fileInfo = new \SplFileInfo($filePath);
            $extensionSuffix = '';
            if ('' !== $fileInfo->getExtension()) {
                $extensionSuffix = '.' . $fileInfo->getExtension();
            }

            $filePath = sprintf(
                '%s%s%s_%d%s',
                $fileInfo->getPath(),
                DIRECTORY_SEPARATOR,
                $fileInfo->getBasename($extensionSuffix),
                $fileNumber,
                $extensionSuffix
            );
        }

        return $filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles(): array
    {
        return $this->writtenFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function areSeveralFilesNeeded(): bool
    {
        $maxLinesPerFile = $this->getMaxLinesPerFile();
        if (-1 === $maxLinesPerFile) {
            return false;
        }

        return $this->stepExecution->getTotalItems() > $maxLinesPerFile;
    }

    private function getMaxLinesPerFile(): int
    {
        $parameters = $this->getStepExecution()->getJobParameters();

        return $parameters->has('linesPerFile') ? (int) $parameters->get('linesPerFile') : -1;
    }

    private function getStepExecution(): StepExecution
    {
        if (!$this->stepExecution instanceof StepExecution) {
            throw new \Exception('Reader have not been properly initialized');
        }

        return $this->stepExecution;
    }

    private function addHeadersIfNeeded(array $item): void
    {
        $parameters = $this->getStepExecution()->getJobParameters();
        if (!$parameters->has('withHeader') || $parameters->get('withHeader') === false) {
            return;
        }

        $this->writer->addRow(array_keys($item));
    }

    private function isMaxLinesPerFileReached(): bool
    {
        if (!$this->areSeveralFilesNeeded()) {
            return false;
        }

        return $this->numberOfWrittenLines > 0 && $this->numberOfWrittenLines % $this->getMaxLinesPerFile() === 0;
    }

    /**
     * @var FileToExport[] $filesToWrite
     */
    private function writeMedia(array $filesToWrite): void
    {
        if (empty($filesToWrite)) {
            return;
        }

        $parameters = $this->getStepExecution()->getJobParameters();
        if (!$parameters->has('with_media') || !$parameters->get('with_media')) {
            return;
        }

        foreach ($filesToWrite as $fileToWrite) {
            $this->writtenFiles[] = WrittenFileInfo::fromFileStorage($fileToWrite->getKey(), $fileToWrite->getStorage(), $fileToWrite->getPath());
        }
    }
}
