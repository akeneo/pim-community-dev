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

use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\RowPayload;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\FileIteratorFactory;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\FileIteratorInterface;
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
    private ?StepExecution $stepExecution = null;
    private ?FileIteratorInterface $fileIterator = null;
    private ?ColumnCollection $columnCollection = null;

    public function __construct(
        private string $fileType,
        private FileIteratorFactory $flatFileIteratorFactory,
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

        $rowPosition = $this->fileIterator->key();
        $this->fileIterator->next();
        $this->checkColumnNumber($currentProductLine);

        return new RowPayload(
            new Row(array_combine($this->columnCollection->getColumnUuids(), $currentProductLine)),
            $this->columnCollection,
            $rowPosition,
        );
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
            $fileHeaders->assertColumnMatch($this->columnCollection);
        }
    }

    public function flush(): void
    {
        $this->fileIterator = null;
    }

    private function createFileIterator(): FileIteratorInterface
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get('storage')['file_path'];

        $fileIterator = $this->flatFileIteratorFactory->create(
            $this->fileType,
            $filePath,
            FileStructure::createFromNormalized($jobParameters->get('file_structure')),
        );
        $fileIterator->rewind();

        return $fileIterator;
    }

    protected function checkColumnNumber(array $productLine): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $headerCount = count($this->fileIterator->getHeaders());
        $columnCount = count($productLine);

        if ($headerCount < count($productLine)) {
            $filePath = $jobParameters->get('storage')['file_path'];

            throw new InvalidItemException('pim_connector.steps.file_reader.invalid_item_columns_count', new FileInvalidItem($productLine, $this->stepExecution->getSummaryInfo('item_position')), ['%totalColumnsCount%' => $headerCount, '%itemColumnsCount%' => $columnCount, '%filePath%' => $filePath, '%lineno%' => $this->fileIterator->key()]);
        }
    }
}
