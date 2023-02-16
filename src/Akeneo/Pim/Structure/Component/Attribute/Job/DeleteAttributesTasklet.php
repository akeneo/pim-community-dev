<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Attribute\Job;

use Akeneo\Pim\Structure\Component\Exception\AttributeRemovalException;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAttributesTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly RemoverInterface $remover,
        private readonly TranslatorInterface $translator,
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
                sprintf('In order to execute "%s" you need to set a step execution.', static::class),
            );
        }

        $attributes = $this->getAttributes();

        $this->stepExecution->setTotalItems(count($attributes));
        $this->stepExecution->addSummaryInfo('deleted_attributes', 0);
        $this->stepExecution->addSummaryInfo('skipped_attributes', 0);

        foreach ($attributes as $attribute) {
            $this->delete($attribute);
        }
    }

    /**
     * @return Attribute[]
     */
    private function getAttributes(): array
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        return match ($filters['operator']) {
            'IN' => $this->attributeRepository->findByCodes($filters['values']),
            'NOT IN' => $this->attributeRepository->findByNotInCodes($filters['values']),
            default => new \LogicException('Operator should be "IN" or "NOT IN"'),
        };
    }

    private function delete(Attribute $attribute): void
    {
        try {
            $this->remover->remove($attribute);
            $this->stepExecution->incrementSummaryInfo('deleted_attributes');
            $this->stepExecution->incrementProcessedItems();
        } catch (AttributeRemovalException $e) {
            $message = $this->translator->trans($e->messageTemplate, $e->messageParameters);
            $this->addWarning($message, $attribute);
            $this->stepExecution->incrementSummaryInfo('skipped_attributes');
        }
    }

    private function addWarning(string $reason, Attribute $attribute): void
    {
        $this->stepExecution->addWarning(
            $this->translator->trans($reason),
            [],
            new DataInvalidItem($attribute),
        );
    }

    public function isTrackable(): bool
    {
        return true;
    }
}
