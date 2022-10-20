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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractedMedia;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\ProcessedTailoredExport;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Writer\WriterInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractItemMediaWriter implements ItemWriterInterface, InitializableInterface, FlushableInterface, StepExecutionAwareInterface, ArchivableWriterInterface
{
    private const DATETIME_FORMAT = 'Y-m-d_H-i-s';
    private ?StepExecution $stepExecution = null;

    /** @var WrittenFileInfo[] */
    private array $writtenFiles = [];
    private int $numberOfWrittenLines = 0;
    private ?string $openedPath = null;
    private ?WriterInterface $writer = null;

    public function __construct(
        private Filesystem $localFileSystem,
        private FileWriterFactory $fileWriterFactory,
    ) {
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
     *
     * @param array<ProcessedTailoredExport> $items
     */
    public function write(array $items): void
    {
        if (!empty($items) && 0 === $this->numberOfWrittenLines) {
            $this->openedPath = $this->getPath();
            $this->writer = $this->fileWriterFactory->build($this->getWriterOptions());
            $this->writer->openToFile($this->openedPath);
            $this->addHeadersIfNeeded(current($items)->getItems());
        }

        foreach ($items as $processedTailoredExport) {
            if ($this->isMaxLinesPerFileReached()) {
                $this->writer->close();
                $this->writtenFiles[] = WrittenFileInfo::fromLocalFile($this->openedPath, basename($this->openedPath));

                $this->writer = $this->fileWriterFactory->build($this->getWriterOptions());
                $this->openedPath = $this->getPath();
                $this->writer->openToFile($this->openedPath);
                $this->addHeadersIfNeeded($processedTailoredExport->getItems());
            }

            $this->writer->addRow(WriterEntityFactory::createRowFromArray($processedTailoredExport->getItems()));
            $this->writeMedia($processedTailoredExport->getExtractedMediaCollection());
            ++$this->numberOfWrittenLines;
        }

        $this->stepExecution->incrementSummaryInfo('write', count($items));
    }

    /**
     * Flush items into a file.
     */
    public function flush(): void
    {
        if (0 !== $this->numberOfWrittenLines && null !== $this->openedPath) {
            $this->writtenFiles[] = WrittenFileInfo::fromLocalFile($this->openedPath, basename($this->openedPath));
        }

        if (null !== $this->writer) {
            $this->writer->close();
        }
    }

    /**
     * Get the file path in which to write the data.
     */
    public function getPath(): string
    {
        $jobParameters = $this->getStepExecution()->getJobParameters();
        $jobExecution = $this->getStepExecution()->getJobExecution();

        $storage = $jobParameters->get('storage');
        $filePath = LocalStorage::TYPE === $storage['type']
            ? $storage['file_path']
            : sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, $storage['file_path']);

        if (str_contains($filePath, '%')) {
            $jobLabel = '';
            $datetime = $this->getStepExecution()->getStartTime()->format(self::DATETIME_FORMAT);
            if (null !== $jobExecution->getJobInstance()) {
                $jobLabel = preg_replace('#[^A-Za-z0-9.]#', '_', $jobExecution->getJobInstance()->getLabel());
            }

            $filePath = strtr($filePath, ['%datetime%' => $datetime, '%job_label%' => $jobLabel]);
        }

        if ($this->areSeveralFilesNeeded()) {
            $fileNumber = floor($this->numberOfWrittenLines / $this->getMaxLinesPerFile()) + 1;
            $fileInfo = new \SplFileInfo($filePath);
            $extensionSuffix = '';
            if ('' !== $fileInfo->getExtension()) {
                $extensionSuffix = '.'.$fileInfo->getExtension();
            }

            $filePath = sprintf(
                '%s%s%s_%d%s',
                $fileInfo->getPath(),
                DIRECTORY_SEPARATOR,
                $fileInfo->getBasename($extensionSuffix),
                $fileNumber,
                $extensionSuffix,
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

    protected function getStepExecution(): StepExecution
    {
        if (!$this->stepExecution instanceof StepExecution) {
            throw new \Exception('Reader have not been properly initialized');
        }

        return $this->stepExecution;
    }

    protected function getWriterOptions(): array
    {
        return [];
    }

    private function addHeadersIfNeeded(array $item): void
    {
        $parameters = $this->getStepExecution()->getJobParameters();
        if (!$parameters->has('withHeader') || false === $parameters->get('withHeader')) {
            return;
        }

        $this->writer->addRow(WriterEntityFactory::createRowFromArray(array_keys($item)));
    }

    private function isMaxLinesPerFileReached(): bool
    {
        if (!$this->areSeveralFilesNeeded()) {
            return false;
        }

        return $this->numberOfWrittenLines > 0 && 0 === $this->numberOfWrittenLines % $this->getMaxLinesPerFile();
    }

    /**
     * @param ExtractedMedia[] $extractedMediaCollection
     */
    private function writeMedia(array $extractedMediaCollection): void
    {
        if (empty($extractedMediaCollection)) {
            return;
        }

        $parameters = $this->getStepExecution()->getJobParameters();
        if (!$parameters->has('with_media') || !$parameters->get('with_media')) {
            return;
        }

        foreach ($extractedMediaCollection as $mediaToWrite) {
            $this->writtenFiles[] = WrittenFileInfo::fromFileStorage(
                $mediaToWrite->getKey(),
                $mediaToWrite->getStorage(),
                $mediaToWrite->getPath(),
            );
        }
    }
}
