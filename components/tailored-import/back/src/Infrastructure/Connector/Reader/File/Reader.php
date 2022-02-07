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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader\File;

use Akeneo\Platform\TailoredImport\Application\Common\ColumnCollection;
use Akeneo\Platform\TailoredImport\Application\Common\Row;
use Akeneo\Platform\TailoredImport\Application\ReadFile\FileHeaderCollection;
use Akeneo\Platform\TailoredImport\Infrastructure\FlatFileIterator\FlatFileIteratorInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

// TODO implement TrackableItemReaderInterface
class Reader implements ItemReaderInterface, StepExecutionAwareInterface, InitializableInterface, FlushableInterface
{
    private StepExecution $stepExecution;
    private ?FlatFileIteratorInterface $fileIterator = null;

    public function __construct(
        private string $fileType,
        private FlatFileIteratorFactory $flatFileIteratorFactory,
    ) {
    }

    public function read()
    {
        $this->fileIterator->next();

        if ($this->fileIterator->valid()) {
            $this->stepExecution->incrementSummaryInfo('item_position');
        }

        $currentProductLine = $this->fileIterator->current();

        if (null === $currentProductLine) {
            return null;
        }

        // Should we check if the numbers of cells in product line is equals to the number of columns/headers ?
        // TODO format cell to string (instead of DateTime, number, bool)

        $normalizedColumns = $this->stepExecution->getJobParameters()->get('import_structure')['columns'];
        $columns = ColumnCollection::createFromNormalized($normalizedColumns);

        return new Row(array_combine($columns->getColumnUuids(), $currentProductLine));
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function initialize(): void
    {
        if (null === $this->fileIterator) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $this->fileIterator = $this->flatFileIteratorFactory->create($this->fileType, $jobParameters);
            $this->fileIterator->rewind();

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
        $normalizedColumns = $this->stepExecution->getJobParameters()->get('import_structure')['columns'];
        $columns = ColumnCollection::createFromNormalized($normalizedColumns);

        if (!$fileHeaders->matchToColumnCollection($columns)) {
            throw new InvalidFileHeadersException();
        }
    }
}
