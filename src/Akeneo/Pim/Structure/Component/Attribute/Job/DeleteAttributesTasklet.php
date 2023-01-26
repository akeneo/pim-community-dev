<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Attribute\Job;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAttributesTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly BulkRemoverInterface $remover,
        private readonly EntityManagerClearerInterface $cacheClearer,
        private readonly int $batchSize = 100,
    ) {

    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(
                sprintf('In order to execute "%s" you need to set a step execution.', static::class)
            );
        }

        $attributesToDelete = $this->getAttributesToDelete();

        $this->stepExecution->setTotalItems(count($attributesToDelete));
        $this->stepExecution->addSummaryInfo('deleted_attributes', 0);

        foreach (array_chunk($attributesToDelete, $this->batchSize) as $batchAttributes) {
            $this->delete($batchAttributes);
        }
    }

    /**
     * @param Attribute[] $attributes
     */
    private function delete(array $attributes): void
    {
        $this->remover->removeAll($attributes);

        $this->stepExecution->incrementSummaryInfo('deleted_attributes', count($attributes));
        $this->stepExecution->incrementProcessedItems(count($attributes));

        $this->cacheClearer->clear();
    }

    /**
     * @return Attribute[]
     */
    private function getAttributesToDelete(): array
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        return match ($filters['operator']) {
            'IN' => $this->attributeRepository->findByCodes($filters['values']),
            'NOT IN' => $this->attributeRepository->findByNotInCodes($filters['values']),
            default => new \LogicException('Operator should be "IN" or "NOT IN"'),
        };
    }

    public function isTrackable(): bool
    {
        return true;
    }
}
