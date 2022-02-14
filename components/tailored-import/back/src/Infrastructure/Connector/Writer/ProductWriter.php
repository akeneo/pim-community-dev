<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Writer;

use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Platform\TailoredImport\Application\Common\Column;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\RowPayload;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Webmozart\Assert\Assert;

class ProductWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution;

    public function __construct(
        private UpsertProductHandler $upsertProductHandler,
    ) {
    }

    public function write(array $items): void
    {
        Assert::allIsInstanceOf($items, RowPayload::class);

        /** @var RowPayload $rowPayload */
        foreach ($items as $rowPayload) {
            try {
                ($this->upsertProductHandler)($rowPayload->getUpsertProductCommand());
            } catch (LegacyViolationsException $legacyViolationsException) {
                foreach ($legacyViolationsException->violations() as $violation) {
                    $this->stepExecution->addWarning($violation->getMessage(), $violation->getParameters(), new FileInvalidItem($this->getFormattedCells($rowPayload), $rowPayload->getRowPosition()));
                }
            }
        }
    }

    private function getFormattedCells(RowPayload $rowPayload): array
    {
        $formattedCells = [];
        $columns = $rowPayload->getColumnCollection()->getIterator();
        /** @var Column $column */
        foreach ($columns as $column) {
            $formattedCells[$column->label()] = $rowPayload->getRow()->getCellData($column->uuid());
        }

        return $formattedCells;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
