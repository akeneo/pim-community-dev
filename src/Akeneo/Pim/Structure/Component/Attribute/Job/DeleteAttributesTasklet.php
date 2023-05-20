<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Attribute\Job;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\AttributeRemovalSubscriber;
use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAttributesTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly SearchableRepositoryInterface $attributeRepository,
        private readonly RemoverInterface $remover,
        private readonly TranslatorInterface $translator,
        private readonly AttributeRemovalSubscriber $attributeRemovalSubscriber,
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

        $this->attributeRemovalSubscriber->flushEvents();
    }

    /**
     * @return Attribute[]
     */
    private function getAttributes(): array
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        return $this->attributeRepository->findBySearch($filters['search'], $filters['options']);
    }

    private function delete(Attribute $attribute): void
    {
        try {
            $this->remover->remove($attribute);
            $this->stepExecution->incrementSummaryInfo('deleted_attributes');
        } catch (CannotRemoveAttributeException $e) {
            $this->addWarning($this->translator->trans($e->messageTemplate, $e->messageParameters), $attribute->getCode());
            $this->stepExecution->incrementSummaryInfo('skipped_attributes');
        } catch (\Exception $e) {
            $this->addWarning($e->getMessage(), $attribute->getCode());
            $this->stepExecution->incrementSummaryInfo('skipped_attributes');
        }

        $this->stepExecution->incrementProcessedItems();
    }

    private function addWarning(string $reason, string $attributeCode): void
    {
        $this->stepExecution->addWarning($reason, [], new DataInvalidItem(['code' => $attributeCode]));
    }

    public function isTrackable(): bool
    {
        return true;
    }
}
