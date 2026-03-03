<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        private readonly SimpleFactoryInterface $channelFactory,
        private readonly ObjectUpdaterInterface $channelUpdater,
        private readonly ValidatorInterface $validator,
        private readonly ObjectDetacherInterface $objectDetacher
    ) {
        parent::__construct($repository);
    }

    public function process($item)
    {
        $itemIdentifier = $this->getItemIdentifier($this->repository, $item);
        $channel = $this->findOrCreateObject($itemIdentifier);

        try {
            $this->channelUpdater->update($channel, $item);
        } catch (PropertyException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($channel);
        if ($violations->count() > 0) {
            if (null === $channel->getId()) {
                foreach ($channel->getLocales() as $locale) {
                    $channel->removeLocale($locale);
                }
            }

            $this->objectDetacher->detach($channel);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        if (null !== $this->stepExecution) {
            $this->saveProcessedItemInStepExecutionContext($itemIdentifier, $channel);
        }

        return $channel;
    }

    private function saveProcessedItemInStepExecutionContext(string $itemIdentifier, mixed $processedItem)
    {
        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];
        $processedItemsBatch[$itemIdentifier] = $processedItem;

        $executionContext->put('processed_items_batch', $processedItemsBatch);
    }

    private function findOrCreateObject(string $itemIdentifier)
    {
        $entity = $this->repository->findOneByIdentifier($itemIdentifier);
        if (null === $entity) {
            if ('' === $itemIdentifier || null === $this->stepExecution) {
                return $this->channelFactory->create();
            }

            $executionContext = $this->stepExecution->getExecutionContext();
            $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];

            return $processedItemsBatch[$itemIdentifier] ?? $this->channelFactory->create();
        }

        return $entity;
    }
}
