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
use Akeneo\Platform\TailoredImport\Infrastructure\Subscriber\UpdateJobExecutionSummarySubscriber;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

class ProductWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface
{
    private ?StepExecution $stepExecution;

    public function __construct(
        private MessageBusInterface $messageBus,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function initialize(): void
    {
        $this->eventDispatcher->addSubscriber(new UpdateJobExecutionSummarySubscriber($this->stepExecution));
    }

    public function write(array $items): void
    {
        Assert::allIsInstanceOf($items, RowPayload::class);

        /** @var RowPayload $rowPayload */
        foreach ($items as $rowPayload) {
            $this->upsertProduct($rowPayload);
        }

        $skippedNoDiffDuringThisBatch = $this->calculateSkippedNoDiff($this->stepExecution);

        $this->stepExecution->incrementSummaryInfo('skipped_no_diff', $skippedNoDiffDuringThisBatch);
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
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

            if ($this->shouldSkipProduct($violationsException)) {
                $this->stepExecution->incrementSummaryInfo('skip');

                return;
            }

            $this->upsertProductWithSkippedValues($rowPayload, $violationsException);
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
            $formattedCells[$column->getLabel()] = $rowPayload->getRow()->getCellData($column->getUuid())->getValue();
        }

        return $formattedCells;
    }

    private function shouldSkipProduct(ViolationsException|LegacyViolationsException $violationsException): bool
    {
        if (
            $violationsException instanceof LegacyViolationsException ||
            'skip_product' === $this->stepExecution->getJobParameters()->get('error_action')
        ) {
            return true;
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($violationsException->violations() as $violation) {
            if ('' === $violation->getPropertyPath()) {
                return true;
            }
        }

        return false;
    }

    private function upsertProductWithSkippedValues(
        RowPayload $rowPayload,
        ViolationsException $violationsException,
    ): void {
        $userIntentCountBeforeClean = $this->getUserIntentCount($rowPayload);
        $rowPayload = $this->removeInvalidUserIntents($rowPayload, $violationsException);
        $userIntentCountAfterClean = $this->getUserIntentCount($rowPayload);

        if (0 === $userIntentCountAfterClean || $userIntentCountAfterClean === $userIntentCountBeforeClean) {
            $this->stepExecution->incrementSummaryInfo('skip');

            return;
        }

        $this->upsertProduct($rowPayload);
    }

    private function removeInvalidUserIntents(
        RowPayload $rowPayload,
        ViolationsException $violationsException,
    ): RowPayload {
        $rowPayload->setUpsertProductCommand(
            UpsertProductCommandCleaner::removeInvalidUserIntents(
                array_map(
                    static fn (ConstraintViolationInterface $violation) => $violation->getPropertyPath(),
                    iterator_to_array($violationsException->violations()),
                ),
                $rowPayload->getUpsertProductCommand(),
            ),
        );

        return $rowPayload;
    }

    private function getUserIntentCount(RowPayload $rowPayload): int
    {
        $valueUserIntentCount = count($rowPayload->getUpsertProductCommand()->valueUserIntents());
        $categoryUserIntentCount = null === $rowPayload->getUpsertProductCommand()->categoryUserIntent() ? 0 : 1;

        return $valueUserIntentCount + $categoryUserIntentCount;
    }

    private function calculateSkippedNoDiff(StepExecution $stepExecution): int
    {
        return $stepExecution->getSummaryInfo('item_position', 0)
            - $stepExecution->getSummaryInfo('create', 0)
            - $stepExecution->getSummaryInfo('process', 0)
            - $stepExecution->getSummaryInfo('skip', 0)
            - $stepExecution->getSummaryInfo('skipped_no_diff', 0);
    }
}
