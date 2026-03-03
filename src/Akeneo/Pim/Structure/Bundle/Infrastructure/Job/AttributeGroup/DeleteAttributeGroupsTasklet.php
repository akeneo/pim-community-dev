<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup;

use Akeneo\Pim\Structure\Component\Exception\UserFacingError;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;

final class DeleteAttributeGroupsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly AttributeGroupRepositoryInterface $attributeGroupRepository,
        private readonly RemoverInterface $remover,
        private readonly EntityManagerClearerInterface $cacheClearer,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly JobStopper $jobStopper,
        private readonly int $batchSize = 100,
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

        $this->stepExecution->setTotalItems(count($attributeGroupCodesToDelete));
        $this->stepExecution->addSummaryInfo('deleted_attribute_groups', 0);
        $this->stepExecution->addSummaryInfo('skipped_attribute_groups', 0);

        foreach (array_chunk($attributeGroupCodesToDelete, $this->batchSize) as $attributeGroupCodes) {
            $this->deleteAttributeGroups($attributeGroupCodes);
            if ($this->jobStopper->isStopping($this->stepExecution)) {
                $this->jobStopper->stop($this->stepExecution);
                return;
            }

            $this->cacheClearer->clear();
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }

    /**
     * @return AttributeGroup[]
     */
    private function getAttributeGroups(array $attributeCodes): array
    {
        return $this->attributeGroupRepository->findBy(['code' => $attributeCodes]);
    }

    /**
     * @param string[] $attributeGroupCodes
     */
    private function deleteAttributeGroups(array $attributeGroupCodes): void
    {
        $attributeGroups = $this->getAttributeGroups($attributeGroupCodes);

        foreach ($attributeGroups as $attributeGroup) {
            $this->deleteAttributeGroup($attributeGroup);
        }
    }

    private function deleteAttributeGroup(AttributeGroup $attributeGroup): void
    {
        try {
            $this->remover->remove($attributeGroup);
            $this->stepExecution->incrementSummaryInfo('deleted_attribute_groups');
        } catch (UserFacingError $e) {
            $this->stepExecution->incrementSummaryInfo('skipped_attribute_groups');
            $this->stepExecution->addWarning($e->translationKey(), $e->translationParameters(), new DataInvalidItem([
                'code' => $attributeGroup->getCode()
            ]));
        }

        $this->stepExecution->incrementProcessedItems();
    }

    public function isTrackable(): bool
    {
        return true;
    }
}
