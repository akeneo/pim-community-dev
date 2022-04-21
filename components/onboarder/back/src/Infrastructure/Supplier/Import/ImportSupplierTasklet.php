<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Import;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierExists;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code;
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
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImportSupplierTasklet implements TaskletInterface
{
    private const CLEAR_FREQUENCY = 100;

    private ItemReaderInterface $reader;
    private ValidatorInterface $validator;
    private CreateSupplierHandler $createSupplierHandler;
    private UpdateSupplierHandler $updateSupplierHandler;
    private EntityManagerClearerInterface $unitOfWorkAndRepositoriesClearer;
    private SupplierExists $supplierExists;
    private JobRepositoryInterface $jobRepository;
    private EventDispatcherInterface $eventDispatcher;
    private ?StepExecution $stepExecution;

    public function __construct(
        ItemReaderInterface $reader,
        ValidatorInterface $validator,
        CreateSupplierHandler $createSupplierHandler,
        UpdateSupplierHandler $updateSupplierHandler,
        EntityManagerClearerInterface $unitOfWorkAndRepositoriesClearer,
        SupplierExists $supplierExists,
        JobRepositoryInterface $jobRepository,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->reader = $reader;
        $this->validator = $validator;
        $this->createSupplierHandler = $createSupplierHandler;
        $this->updateSupplierHandler = $updateSupplierHandler;
        $this->unitOfWorkAndRepositoriesClearer = $unitOfWorkAndRepositoriesClearer;
        $this->supplierExists = $supplierExists;
        $this->jobRepository = $jobRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        if ($this->reader instanceof StepExecutionAwareInterface) {
            $this->reader->setStepExecution($this->stepExecution);
        }

        $itemsProcessed = 0;

        while (true) {
            try {
                $supplierData = $this->reader->read();

                if (null === $supplierData) {
                    break;
                }

                if (!$this->supplierExists->fromCode(Code::fromString($supplierData['supplier_code']))) {
                    $this->createSupplier($supplierData);
                    $this->stepExecution->incrementSummaryInfo('create');
                } else {
                    $this->updateSupplier($supplierData);
                    $this->stepExecution->incrementSummaryInfo('process');
                }
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($this->stepExecution, $this->reader, $e);

                continue;
            }

            $itemsProcessed++;
            if (0 === $itemsProcessed % static::CLEAR_FREQUENCY) {
                $this->unitOfWorkAndRepositoriesClearer->clear();
            }
        }

        $this->unitOfWorkAndRepositoriesClearer->clear();
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function createSupplier(array $supplierData): void
    {
        $command = new CreateSupplier(
            Uuid::uuid4()->toString(),
            $supplierData['supplier_code'],
            $supplierData['supplier_label'],
            $supplierData['contributor_emails'],
        );

        $errors = $this->validator->validate($command);

        if ($errors->count() > 0) {
            $this->skipItemWithConstraintViolations($supplierData, $errors);
        }

        ($this->createSupplierHandler)($command);
    }

    private function updateSupplier(array $supplierData): void
    {
        $command = new UpdateSupplier(
            Uuid::uuid4()->toString(),
            $supplierData['supplier_label'],
            $supplierData['contributor_emails'],
        );

        $errors = $this->validator->validate($command);

        if ($errors->count() > 0) {
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
        \Exception $previousException = null,
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
            $previousException,
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
