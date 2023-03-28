<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup;

use Akeneo\Pim\Structure\Component\Exception\UserFacingError;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

final class MoveChildAttributesTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly ObjectUpdaterInterface $updater,
        private readonly BulkSaverInterface $saver,
        private readonly EntityManagerClearerInterface $cacheClearer,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly JobStopper $jobStopper,
        private readonly int $batchSize = 1000,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(sprintf('In order to execute "%s" you need to set a step execution.', DeleteAttributeGroupsTasklet::class));
        }

        $attributeGroupCodesToDelete = $this->stepExecution->getJobParameters()->get('filters')['codes'];
        $replacementAttributeGroupCode = $this->stepExecution->getJobParameters()->get('replacement_attribute_group_code');
        $this->stepExecution->addSummaryInfo('moved_attributes', 0);

        foreach ($this->getAttributeToMoveByChunk($attributeGroupCodesToDelete) as $attributeChunk) {
            $this->moveAttributes($attributeChunk, $replacementAttributeGroupCode);
            if ($this->jobStopper->isStopping($this->stepExecution)) {
                $this->jobStopper->stop($this->stepExecution);

                return;
            }
        }

        $this->cacheClearer->clear();
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    private function getAttributeToMoveByChunk(array $attributeGroupCodes): \Iterator
    {
        $searchAfterAttributeCode = null;

        while (true) {
            $attributes = $this->attributeRepository->getAttributesByGroups(
                $attributeGroupCodes,
                $this->batchSize,
                $searchAfterAttributeCode
            );

            if (empty($attributes)) {
                return;
            }

            $searchAfterAttributeCode = end($attributes)->getCode();
            reset($attributes);

            yield $attributes;
        }
    }

    private function moveAttributes(array $attributes, string $replacementAttributeGroupCode)
    {
        foreach ($attributes as $attribute) {
            try {
                $this->updater->update($attribute, ['group' => $replacementAttributeGroupCode]);
                $this->stepExecution->incrementSummaryInfo('moved_attributes');
            } catch (UserFacingError $e) {
                $this->stepExecution->addWarning($e->translationKey(), $e->translationParameters(), new DataInvalidItem([
                    'code' => $attribute->getCode(),
                ]));
            }
            $this->stepExecution->incrementProcessedItems();
        }
        $this->saver->saveAll($attributes);
    }

    public function isTrackable(): bool
    {
        return true;
    }
}
