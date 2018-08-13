<?php

namespace Akeneo\Tool\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Simple import processor
 *
 * @author    Julien Sanchez <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Processor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param SimpleFactoryInterface                $factory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $objectDetacher
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher
    ) {
        parent::__construct($repository);

        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $itemIdentifier = $this->getItemIdentifier($this->repository, $item);
        $entity = $this->findOrCreateObject($itemIdentifier);

        try {
            $this->updater->update($entity, $item);
        } catch (PropertyException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validate($entity);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($entity);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        if (null !== $this->stepExecution) {
            $this->saveProcessedItemInStepExecutionContext($itemIdentifier, $entity);
        }

        return $entity;
    }

    /**
     * @param string $itemIdentifier
     *
     * @return mixed
     */
    protected function findOrCreateObject(string $itemIdentifier)
    {
        $entity = $this->repository->findOneByIdentifier($itemIdentifier);
        if (null === $entity) {
            return $this->createObject($itemIdentifier);
        }

        return $entity;
    }

    /**
     * Creates an empty new object to process.
     * We look first if there is already a processed item save in the execution context for the same identifier.
     *
     * @param string $itemIdentifier
     *
     * @return object
     */
    protected function createObject(string $itemIdentifier)
    {
        if ('' === $itemIdentifier || null === $this->stepExecution) {
            return $this->factory->create();
        }

        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];

        return $processedItemsBatch[$itemIdentifier] ?? $this->factory->create();
    }

    /**
     * Validates the processed entity.
     *
     * @param mixed $entity
     *
     * @return ConstraintViolationListInterface
     */
    protected function validate($entity)
    {
        return $this->validator->validate($entity);
    }

    /**
     * @param string $itemIdentifier
     * @param mixed  $processedItem
     */
    protected function saveProcessedItemInStepExecutionContext(string $itemIdentifier, $processedItem)
    {
        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];
        $processedItemsBatch[$itemIdentifier] = $processedItem;

        $executionContext->put('processed_items_batch', $processedItemsBatch);
    }
}
