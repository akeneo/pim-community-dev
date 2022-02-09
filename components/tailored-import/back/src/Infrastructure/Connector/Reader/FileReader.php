<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https =>//www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader;

use Akeneo\Platform\TailoredImport\Application\Common\ColumnCollection;
use Akeneo\Platform\TailoredImport\Application\Common\Row;
use Akeneo\Platform\TailoredImport\Domain\Exception\MismatchedFileHeadersException;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\FileHeaderCollection;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\FlatFileIteratorFactory;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\FlatFileIteratorInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class FileReader implements ItemReaderInterface, StepExecutionAwareInterface, InitializableInterface, FlushableInterface, TrackableItemReaderInterface
{
    private ?StepExecution $stepExecution;
    private ?FlatFileIteratorInterface $fileIterator = null;
    private ?ColumnCollection $columnCollection = null;

    public function __construct(
        private string $fileType,
        private FlatFileIteratorFactory $flatFileIteratorFactory,
    ) {
    }

    public function totalItems(): int
    {
        $iterator = $this->createFileIterator();

        return max(iterator_count($iterator) - 1, 0);
    }

    public function read()
    {
        if ($this->fileIterator->valid()) {
            $this->stepExecution->incrementSummaryInfo('item_position');
        }

        $currentProductLine = $this->fileIterator->current();
        if (null === $currentProductLine) {
            return null;
        }

        $this->fileIterator->next();
        $this->checkColumnNumber($currentProductLine);

        return new Row(array_combine($this->columnCollection->columnUuids(), $currentProductLine));
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function initialize(): void
    {
        if (null === $this->fileIterator) {
            $normalizedColumns = $this->stepExecution->getJobParameters()->get('import_structure')['columns'];
            $this->fileIterator = $this->createFileIterator();
            $this->columnCollection = ColumnCollection::createFromNormalized($normalizedColumns);

            $fileHeaders = $this->fileIterator->getHeaders();
            $this->checkFileHeaders($fileHeaders);
        }
    }

    public function flush(): void
    {
        $this->fileIterator = null;
    }

    private function checkFileHeaders(FileHeaderCollection $fileHeaders): void
    {
        if (!$fileHeaders->matchToColumnCollection($this->columnCollection)) {
            throw new MismatchedFileHeadersException();
        }
    }

    private function createFileIterator(): FlatFileIteratorInterface
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $this->fileIterator = $this->flatFileIteratorFactory->create($this->fileType, $jobParameters);
        $this->fileIterator->rewind();

        return $this->fileIterator;
    }

    protected function checkColumnNumber(array $productLine): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $headerCount = count($this->fileIterator->getHeaders());
        $columnCount = count($productLine);

        if ($headerCount < count($productLine)) {
            throw new InvalidItemException(
                'pim_connector.steps.file_reader.invalid_item_columns_count',
                new FileInvalidItem($productLine, ($this->stepExecution->getSummaryInfo('item_position'))),
                [
                    '%totalColumnsCount%' => $headerCount,
                    '%itemColumnsCount%'  => $columnCount,
                    '%filePath%' => $jobParameters->get('filePath'),
                    '%lineno%' => $this->fileIterator->key()
                ]
            );
        }
    }
}
