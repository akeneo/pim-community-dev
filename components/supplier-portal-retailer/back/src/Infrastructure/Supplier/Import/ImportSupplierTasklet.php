<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Import;

use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\CreateSupplier\CreateSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\CreateSupplier\CreateSupplierHandler;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\UpdateSupplier\UpdateSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\UpdateSupplier\UpdateSupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetIdentifierFromCode;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\SupplierExists;
use Akeneo\SupplierPortal\Retailer\Infrastructure\SystemClock;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImportSupplierTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution;

    public function __construct(
        private ItemReaderInterface $reader,
        private ValidatorInterface $validator,
        private CreateSupplierHandler $createSupplierHandler,
        private UpdateSupplierHandler $updateSupplierHandler,
        private SupplierExists $supplierExists,
        private JobRepositoryInterface $jobRepository,
        private EventDispatcherInterface $eventDispatcher,
        private GetIdentifierFromCode $getSupplierIdentifierFromSupplierCode,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        if ($this->reader instanceof StepExecutionAwareInterface) {
            $this->reader->setStepExecution($this->stepExecution);
        }

        while (true) {
            try {
                $supplierData = $this->reader->read();

                if (null === $supplierData) {
                    break;
                }

                if (!$this->supplierExists->fromCode($supplierData['supplier_code'])) {
                    $this->createSupplier($supplierData);
                    $this->stepExecution->incrementSummaryInfo('create');
                } else {
                    $this->updateSupplier($supplierData);
                    $this->stepExecution->incrementSummaryInfo('update');
                }
            } catch (InvalidItemException $e) {
                $this->logger->info(
                    sprintf(
                        'An error occurred while importing a supplier: "%s"',
                        $e->getMessage(),
                    ),
                );

                $this->handleStepExecutionWarning($this->stepExecution, $this->reader, $e);

                continue;
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf(
                        'An unhandled exception has been thrown while creating suppliers: "%s"',
                        $e->getMessage(),
                    ),
                );

                continue;
            }
        }
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function createSupplier(array $supplierData): void
    {
        $command = new CreateSupplier(
            $supplierData['supplier_code'],
            $supplierData['supplier_label'],
            $supplierData['contributor_emails'],
            (new SystemClock())->now(),
        );

        $errors = $this->validator->validate($command);

        if (0 < $errors->count()) {
            $this->skipItemWithConstraintViolations($supplierData, $errors);
        }

        ($this->createSupplierHandler)($command);
    }

    private function updateSupplier(array $supplierData): void
    {
        $command = new UpdateSupplier(
            ($this->getSupplierIdentifierFromSupplierCode)($supplierData['supplier_code']),
            $supplierData['supplier_label'],
            $supplierData['contributor_emails'],
            (new SystemClock())->now(),
        );

        $errors = $this->validator->validate($command);

        if (0 < $errors->count()) {
            $this->skipItemWithConstraintViolations($supplierData, $errors);
        }

        ($this->updateSupplierHandler)($command);
    }

    /**
     * Sets an item as skipped and throws an invalid item exception.
     *
     * @throws InvalidItemException
     */
    private function skipItemWithConstraintViolations(
        array $item,
        ConstraintViolationListInterface $violations,
    ): void {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }

        $itemPosition = null !== $this->stepExecution ? $this->stepExecution->getSummaryInfo('item_position') : 0;

        throw new InvalidItemFromViolationsException(
            $violations,
            new FileInvalidItem($item, $itemPosition),
            [],
            0,
        );
    }

    private function handleStepExecutionWarning(
        StepExecution $stepExecution,
        ItemReaderInterface $element,
        InvalidItemException $e,
    ): void {
        $warning = new Warning(
            $stepExecution,
            $e->getMessage(),
            $e->getMessageParameters(),
            $e->getItem()->getInvalidData(),
        );

        $this->jobRepository->addWarning($warning);

        $event = new InvalidItemEvent(
            $e->getItem(),
            get_class($element),
            $e->getMessage(),
            $e->getMessageParameters(),
        );

        $this->eventDispatcher->dispatch($event, EventInterface::INVALID_ITEM);
    }
}
