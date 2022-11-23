<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\InvalidItemsCollector;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\FilesystemOperator;

/**
 * Mutualizes code for writers
 *
 * @author    Soulet Olivier <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractInvalidItemWriter extends AbstractFilesystemArchiver
{
    protected InvalidItemsCollector $collector;
    protected ItemWriterInterface $writer;
    protected FileIteratorFactory $fileIteratorFactory;
    protected DefaultValuesProviderInterface $defaultValuesProvider;
    protected string $invalidItemFileFormat;

    protected int $batchSize = 100;

    public function __construct(
        InvalidItemsCollector $collector,
        ItemWriterInterface $writer,
        FileIteratorFactory $fileIteratorFactory,
        FilesystemOperator $filesystem,
        DefaultValuesProviderInterface $defaultValuesProvider,
        $invalidItemFileFormat
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->filesystem = $filesystem;
        $this->defaultValuesProvider = $defaultValuesProvider;
        $this->invalidItemFileFormat = $invalidItemFileFormat;
    }

    /**
     * {@inheritdoc}
     *
     * Re-parse the imported file and write into a new one the invalid lines.
     */
    public function archive(JobExecution $jobExecution): void
    {
        if (empty($this->collector->getInvalidItems())) {
            return;
        }

        $invalidItemPositions = new ArrayCollection();
        foreach ($this->collector->getInvalidItems() as $invalidItem) {
            if ($invalidItem instanceof FileInvalidItem) {
                $invalidItemPositions->add($invalidItem->getItemPosition());
            }
        }

        $readJobParameters = $jobExecution->getJobParameters();
        $currentItemPosition = 0;
        $itemsToWrite = [];

        $fileIterator = $this->getInputFileIterator($readJobParameters);

        $this->setupWriter($jobExecution);

        while ($fileIterator->valid()) {
            $readItem = $fileIterator->current();

            $currentItemPosition++;

            if ($invalidItemPositions->contains($currentItemPosition)) {
                $headers = $fileIterator->getHeaders();

                $readItem = $this->removeValuesWithEmptyHeaders($readItem, $headers);
                $headers = $this->removeEmptyHeaders($headers);

                $headersLength = count($headers);
                $readItem = $this->padEmptyValuesToReadItem($readItem, $headersLength);
                $readItem = $this->trimTrailingValuesWithoutHeaders($readItem, $headersLength);

                $invalidItem = array_combine($headers, $readItem);
                if (false !== $invalidItem) {
                    $itemsToWrite[] = $invalidItem;
                }

                $invalidItemPositions->removeElement($currentItemPosition);
            }

            if (count($itemsToWrite) > 0 && 0 === count($itemsToWrite) % $this->batchSize) {
                $this->writer->write($itemsToWrite);
                $itemsToWrite = [];
            }

            if ($invalidItemPositions->isEmpty()) {
                break;
            }

            $fileIterator->next();
        }

        if (count($itemsToWrite) > 0) {
            $this->writer->write($itemsToWrite);
        }

        $this->writer->flush();

        $this->putToArchivistFilesystem($jobExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution): bool
    {
        if ($jobExecution->getJobParameters()->has('invalid_items_file_format')) {
            return $this->invalidItemFileFormat === $jobExecution->getJobParameters()->get('invalid_items_file_format');
        }

        return false;
    }

    /**
     * Put the generated invalid items file to the archivist filesystem
     */
    protected function putToArchivistFilesystem(JobExecution $jobExecution): void
    {
        $fileKey = strtr(
            $this->getRelativeArchivePath($jobExecution),
            ['%filename%' => $this->getFilename()]
        );

        $workingDirectory = $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER);
        $localFilePath = $workingDirectory . $this->getFilename();

        if (is_readable($localFilePath)) {
            $localStream = fopen($localFilePath, 'r');

            $this->filesystem->writeStream($fileKey, $localStream);

            if (is_resource($localStream)) {
                fclose($localStream);
            }
        }
    }

    /**
     * Setup the writer with a new JobExecution to write the invalid_items file.
     * We need to setup the writer manually because it's usually set up by the ItemStep.
     */
    protected function setupWriter(JobExecution $jobExecution): void
    {
        $workingDirectory = $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER);
        $localFilePath = $workingDirectory . $this->getFilename();

        $writeParams = $this->defaultValuesProvider->getDefaultValues();
        $writeParams['storage'] = [
            'type' => 'local',
            'file_path' => $localFilePath,
        ];
        $writeParams['withHeader'] = true;

        $writeJobParameters = new JobParameters($writeParams);
        $writeJobExecution = new JobExecution();
        $writeJobExecution->setJobParameters($writeJobParameters);

        $stepExecution = new StepExecution('processor', $writeJobExecution);
        $this->writer->setStepExecution($stepExecution);
        $this->writer->initialize();
    }

    /**
     * Get the input file iterator to iterate on all the items of the file.
     * The returned FileIteratorInterface instance should be position on the first item of the file.
     *
     * @param JobParameters $jobParameters
     *
     * @return FileIteratorInterface
     */
    abstract protected function getInputFileIterator(JobParameters $jobParameters): FileIteratorInterface;

    /**
     * Get the final invalid data filename
     */
    abstract protected function getFilename(): string;

    private function removeValuesWithEmptyHeaders(array $readItem, array $headers): array
    {
        $emptyHeaderKeys = array_keys(array_filter($headers, function (string $columnName) {
            return '' === trim($columnName);
        }));

        foreach ($emptyHeaderKeys as $key) {
            unset($readItem[$key]);
        }

        return $readItem;
    }

    private function removeEmptyHeaders(array $headers): array
    {
        return array_filter($headers, function (string $columnName) {
            return '' !== trim($columnName);
        });
    }

    private function padEmptyValuesToReadItem(array $readItem, int $headersLength): array
    {
        return array_pad($readItem, $headersLength, '');
    }

    private function trimTrailingValuesWithoutHeaders(array $readItem, int $headersLength): array
    {
        return array_slice($readItem, 0, $headersLength);
    }
}
