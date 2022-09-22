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
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

class ProductWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface, FlushableInterface
{
    private const WARNING_BATCH_SIZE = 100;

    private ?StepExecution $stepExecution = null;
    private array $warnings = [];

    public function __construct(
        private MessageBusInterface $messageBus,
        private EventDispatcherInterface $eventDispatcher,
        private JobRepositoryInterface $jobRepository,
    ) {
    }

    public function initialize(): void
    {
        $this->eventDispatcher->addSubscriber(new UpdateJobExecutionSummarySubscriber($this->stepExecution));
    }

    public function flush(): void
    {
        $this->saveAndClearWarnings();
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
        if (!empty($rowPayload->getInvalidValues())) {
            $this->stepExecution->incrementSummaryInfo('skip');
            $this->addInvalidValuesWarning($rowPayload);

            if ('skip_product' === $this->stepExecution->getJobParameters()->get('error_action')) {
                return;
            }
        }

        try {
            if (null === $rowPayload->getUpsertProductCommand()) {
                throw new \RuntimeException('RowPayload wrongly formed missing UpsertCommand');
            }

            $this->messageBus->dispatch($rowPayload->getUpsertProductCommand());
        } catch (LegacyViolationsException|ViolationsException $violationsException) {
            $this->addViolationsWarning($violationsException->violations(), $rowPayload);

            if ($this->shouldSkipProduct($violationsException)) {
                $this->stepExecution->incrementSummaryInfo('skip');

                return;
            }

            $this->upsertProductWithSkippedValues($rowPayload, $violationsException);
        }
    }

    private function addViolationsWarning(ConstraintViolationListInterface $violationList, RowPayload $rowPayload): void
    {
        foreach ($violationList as $violation) {
            $this->addWarning(new Warning(
                $this->stepExecution,
                $violation->getMessage(),
                $violation->getParameters(),
                $this->getFormattedCells($rowPayload),
            ));
        }
    }

    private function addInvalidValuesWarning(RowPayload $rowPayload): void
    {
        foreach ($rowPayload->getInvalidValues() as $invalidValue) {
            $this->addWarning(new Warning(
                $this->stepExecution,
                $invalidValue->getErrorKey(),
                [],
                $this->getFormattedCells($rowPayload),
            ));
        }
    }

    private function addWarning(Warning $warning): void
    {
        $this->warnings[] = $warning;

        if (self::WARNING_BATCH_SIZE <= count($this->warnings)) {
            $this->saveAndClearWarnings();
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
        $command = $rowPayload->getUpsertProductCommand();
        $valueUserIntentCount = count($command->valueUserIntents());
        $categoryUserIntentCount = null === $command->categoryUserIntent() ? 0 : 1;
        $familyUserIntentCount = null === $command->familyUserIntent() ? 0 : 1;
        $enabledUserIntentCount = null === $command->enabledUserIntent() ? 0 : 1;

        return $valueUserIntentCount + $categoryUserIntentCount + $familyUserIntentCount + $enabledUserIntentCount;
    }

    private function calculateSkippedNoDiff(StepExecution $stepExecution): int
    {
        return $stepExecution->getSummaryInfo('item_position', 0)
            - $stepExecution->getSummaryInfo('create', 0)
            - $stepExecution->getSummaryInfo('update', 0)
            - $stepExecution->getSummaryInfo('skip', 0)
            - $stepExecution->getSummaryInfo('skipped_no_diff', 0);
    }

    private function saveAndClearWarnings(): void
    {
        $this->jobRepository->addWarnings($this->stepExecution, $this->warnings);
        $this->warnings = [];
    }
}
