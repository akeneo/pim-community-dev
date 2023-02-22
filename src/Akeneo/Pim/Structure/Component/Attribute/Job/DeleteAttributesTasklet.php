<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Attribute\Job;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\AttributeIsAFamilyVariantAxisInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Doctrine\DBAL\Connection;

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
        private readonly AttributeIsAFamilyVariantAxisInterface $attributeIsAFamilyVariantAxis,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly Connection $dbConnection,
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
        $attributeIdsUsedAsLabelInAFamily = $this->getAllAttributeIdsUsedAsLabelInAFamily();

        $this->stepExecution->setTotalItems(count($attributesToDelete));
        $this->stepExecution->addSummaryInfo('deleted_attributes', 0);

        foreach (array_chunk($attributesToDelete, $this->batchSize) as $batchAttributes) {
            $this->delete($batchAttributes, $attributeIdsUsedAsLabelInAFamily);
        }
    }

    /**
     * @param AttributeInterface[] $attributes
     */
    private function delete(array $attributes, array $attributeIdsUsedAsLabelInAFamily): void
    {
        $initialAttributeCount = count($attributes);

        foreach ($attributes as $key => $attribute) {
            if ($this->isAttributeIdentifier($attribute)
                || $this->isAttributeAFamilyVariantAxis($attribute)
                || $this->isAttributeUsedAsLabelInAFamily($attribute, $attributeIdsUsedAsLabelInAFamily)
                || $this->isAttributeUsedAsChannelConversionUnit($attribute)
            ) {
                unset($attributes[$key]);
            }
        }

        $this->remover->removeAll($attributes);

        $this->stepExecution->incrementSummaryInfo('deleted_attributes', count($attributes));
        $this->stepExecution->incrementProcessedItems($initialAttributeCount);

        $this->cacheClearer->clear();
    }

    private function isAttributeIdentifier(AttributeInterface $attribute): bool
    {
        $isIdentifierType = AttributeTypes::IDENTIFIER === $attribute->getType();

        if ($isIdentifierType) {
            $this->stepExecution->addWarning(
                'flash.attribute.identifier_not_removable',
                [],
                new DataInvalidItem($attribute),
            );
            $this->stepExecution->incrementSummaryInfo('skipped_attributes', 1);
        }

        return $isIdentifierType;
    }

    private function isAttributeAFamilyVariantAxis(AttributeInterface $attribute): bool
    {
        $isAFamilyVariantAxis = $this->attributeIsAFamilyVariantAxis->execute($attribute->getCode());

        if ($isAFamilyVariantAxis) {
            $this->stepExecution->addWarning(
                'pim_enrich.family.info.cant_remove_attribute_used_as_axis',
                [],
                new DataInvalidItem($attribute),
            );
            $this->stepExecution->incrementSummaryInfo('skipped_attributes', 1);
        }

        return $isAFamilyVariantAxis;
    }

    private function isAttributeUsedAsLabelInAFamily(AttributeInterface $attribute, array $attributeIdsUsedAsLabelInAFamily): bool
    {
        $isAttributeUsedAsLabelInAFamily = in_array($attribute->getId(), $attributeIdsUsedAsLabelInAFamily);

        if ($isAttributeUsedAsLabelInAFamily) {
            $this->stepExecution->addWarning(
                'flash.attribute.cant_remove_attributes_used_as_label',
                [],
                new DataInvalidItem($attribute),
            );
            $this->stepExecution->incrementSummaryInfo('skipped_attributes', 1);
        }

        return $isAttributeUsedAsLabelInAFamily;
    }

    private function isAttributeUsedAsChannelConversionUnit(AttributeInterface $attribute): bool
    {
        $channelCodes = [];

        foreach ($this->channelRepository->findAll() as $channel) {
            $attributeCodes = array_keys($channel->getConversionUnits());

            if (in_array($attribute->getCode(), $attributeCodes)) {
                $channelCodes[] = $channel->getCode();
            }
        }

        $isChannelConversionUnit = (bool) count($channelCodes);

        if ($isChannelConversionUnit) {
            $this->stepExecution->addWarning(
                'flash.attribute.used_as_conversion_unit',
                ['%channelCodes%' => join(', ', $channelCodes)],
                new DataInvalidItem($attribute),
            );
            $this->stepExecution->incrementSummaryInfo('skipped_attributes', 1);
        }

        return $isChannelConversionUnit;
    }

    /**
     * @return AttributeInterface[]
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

    private function getAllAttributeIdsUsedAsLabelInAFamily(): array
    {
        // TODO: to move in a dedicated service api ?
        $sql = <<<SQL
            SELECT DISTINCT (label_attribute_id) FROM pim_catalog_family
        SQL;

        return array_map(
            fn (array $result) => $result['label_attribute_id'],
            $this->dbConnection->executeQuery($sql)->fetchAllAssociative()
        );
    }

    public function isTrackable(): bool
    {
        return true;
    }
}
