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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader\File\Xlsx;

use Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader\File\FlatFileIterator;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobParameterException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class Reader implements ItemReaderInterface, StepExecutionAwareInterface, InitializableInterface, FlushableInterface
{
    private StepExecution $stepExecution;
    private ?FileIteratorInterface $fileIterator;

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

        // TODO instantiate the DTO

        return [];
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function initialize(): void
    {
        if (null === $this->fileIterator) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $filePath = $jobParameters->get('filePath');

            // TODO remove this try catch when the file structure will be available in the job parameters
            try {
                $fileStructure = $jobParameters->get('file_structure');
            } catch (UndefinedJobParameterException $exception) {
                $fileStructure = [
                    'header_line' => 0,
                    'header_column' => 0,
                    'product_line' => 1,
                    'product_column' => 0,
                    'sheet_index' => 0,
                ];
            }

            // Should we need to use a Factory like the "legacy" Reader ?
            $this->fileIterator = new FlatFileIterator('xlsx', $filePath, $fileStructure);
            $this->fileIterator->rewind();

            $fileHeaders = $this->fileIterator->getHeaders();

            //TODO validate that fileHeaders match to columns from jobConfiguration
        }
    }

    public function flush(): void
    {
        $this->fileIterator = null;
    }
}
