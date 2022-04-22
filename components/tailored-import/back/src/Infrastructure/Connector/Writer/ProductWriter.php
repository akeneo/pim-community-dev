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

use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Platform\TailoredImport\Domain\Model\Column;
use Akeneo\Platform\TailoredImport\Domain\UpsertProductCommandCleaner;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\RowPayload;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

class ProductWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution;

    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function write(array $items): void
    {
        Assert::allIsInstanceOf($items, RowPayload::class);

        /** @var RowPayload $rowPayload */
        foreach ($items as $rowPayload) {
            $this->upsertProduct($rowPayload);
        }
    }

    private function upsertProduct(RowPayload $rowPayload): void
    {
        try {
            if (null === $rowPayload->getUpsertProductCommand()) {
                throw new \RuntimeException('RowPayload wrongly formed missing UpsertCommand');
            }

            $this->messageBus->dispatch($rowPayload->getUpsertProductCommand());
        } catch (LegacyViolationsException|ViolationsException $violationsException) {
            $this->addWarning($violationsException->violations(), $rowPayload);

            if ('skip_value' === $this->stepExecution->getJobParameters()->get('error_action')
                && $violationsException instanceof ViolationsException
            ) {
                $initialUserIntentsCount = count($rowPayload->getUpsertProductCommand()->valueUserIntents());

                $rowPayload->setUpsertProductCommand(
                    UpsertProductCommandCleaner::removeInvalidUserIntents($violationsException, $rowPayload->getUpsertProductCommand()),
                );

                if (count($rowPayload->getUpsertProductCommand()->valueUserIntents()) < $initialUserIntentsCount) {
                    $this->upsertProduct($rowPayload);
                }
            }
        }
    }

    private function addWarning(ConstraintViolationListInterface $violationList, RowPayload $rowPayload): void
    {
        foreach ($violationList as $violation) {
            $this->stepExecution->addWarning(
                $violation->getMessage(),
                $violation->getParameters(),
                new FileInvalidItem($this->getFormattedCells($rowPayload), $rowPayload->getRowPosition()),
            );
        }
    }

    private function getFormattedCells(RowPayload $rowPayload): array
    {
        $formattedCells = [];
        $columns = $rowPayload->getColumnCollection()->getIterator();
        /** @var Column $column */
        foreach ($columns as $column) {
            $formattedCells[$column->getLabel()] = $rowPayload->getRow()->getCellData($column->getUuid());
        }

        return $formattedCells;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
