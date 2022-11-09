<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Hydrator\OperationCollectionHydratorInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CategoriesReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ChangeCaseOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToDateOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToMeasurementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToNumberOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToPriceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\EnabledReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\FamilyReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\MultiReferenceEntityReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\MultiSelectReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\RemoveWhitespaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SearchAndReplaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SearchAndReplaceValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleReferenceEntityReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleSelectReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Ramsey\Uuid\Uuid;

class OperationCollectionHydrator implements OperationCollectionHydratorInterface
{
    public function hydrate(array $normalizedTarget, array $normalizedOperations): OperationCollection
    {
        return match ($normalizedTarget['type']) {
            AttributeTarget::TYPE => $this->hydrateAttribute($normalizedTarget, $normalizedOperations),
            PropertyTarget::TYPE => $this->hydrateProperty($normalizedTarget['code'], $normalizedOperations),
            default => throw new \InvalidArgumentException(sprintf('Unsupported "%s" Target type', $normalizedTarget['type'])),
        };
    }

    private function hydrateAttribute(array $normalizedTarget, array $normalizedOperations): OperationCollection
    {
        $requiredOperations = $this->getAttributeRequiredOperations($normalizedTarget);
        $configuredOperations = $this->getConfiguredOperations($normalizedOperations);

        return OperationCollection::create(array_merge($requiredOperations, $configuredOperations));
    }

    private function hydrateProperty(string $propertyCode, array $normalizedOperations): OperationCollection
    {
        $configuredOperations = $this->getConfiguredOperations($normalizedOperations);

        return OperationCollection::create($configuredOperations);
    }

    private function getConfiguredOperations(array $normalizedOperations): array
    {
        return \array_map(
            static fn (array $normalizedOperation) => match ($normalizedOperation['type']) {
                BooleanReplacementOperation::TYPE => new BooleanReplacementOperation($normalizedOperation['uuid'], $normalizedOperation['mapping']),
                EnabledReplacementOperation::TYPE => new EnabledReplacementOperation($normalizedOperation['uuid'], $normalizedOperation['mapping']),
                CleanHTMLOperation::TYPE => new CleanHTMLOperation($normalizedOperation['uuid'], $normalizedOperation['modes']),
                SplitOperation::TYPE => new SplitOperation($normalizedOperation['uuid'], $normalizedOperation['separator']),
                SimpleSelectReplacementOperation::TYPE => new SimpleSelectReplacementOperation($normalizedOperation['uuid'], $normalizedOperation['mapping']),
                MultiSelectReplacementOperation::TYPE => new MultiSelectReplacementOperation($normalizedOperation['uuid'], $normalizedOperation['mapping']),
                CategoriesReplacementOperation::TYPE => new CategoriesReplacementOperation($normalizedOperation['uuid'], $normalizedOperation['mapping']),
                FamilyReplacementOperation::TYPE => new FamilyReplacementOperation($normalizedOperation['uuid'], $normalizedOperation['mapping']),
                ChangeCaseOperation::TYPE => new ChangeCaseOperation($normalizedOperation['uuid'], $normalizedOperation['mode']),
                RemoveWhitespaceOperation::TYPE => new RemoveWhitespaceOperation($normalizedOperation['uuid'], $normalizedOperation['modes']),
                SimpleReferenceEntityReplacementOperation::TYPE => new SimpleReferenceEntityReplacementOperation($normalizedOperation['uuid'], $normalizedOperation['mapping']),
                MultiReferenceEntityReplacementOperation::TYPE => new MultiReferenceEntityReplacementOperation($normalizedOperation['uuid'], $normalizedOperation['mapping']),
                SearchAndReplaceOperation::TYPE => self::hydrateSearchAndReplaceOperation($normalizedOperation),
                default => throw new \InvalidArgumentException(sprintf('Unsupported "%s" Operation type', $normalizedOperation['type'])),
            },
            $normalizedOperations,
        );
    }

    private static function hydrateSearchAndReplaceOperation(array $normalizedOperation): SearchAndReplaceOperation
    {
        $replacements = array_map(
            static fn (array $replacement) => new SearchAndReplaceValue(
                $replacement['uuid'],
                $replacement['what'],
                $replacement['with'],
                (bool) $replacement['case_sensitive'],
            ),
            $normalizedOperation['replacements'],
        );

        return new SearchAndReplaceOperation($normalizedOperation['uuid'], $replacements);
    }

    private function getAttributeRequiredOperations(array $normalizedTarget): array
    {
        return match ($normalizedTarget['attribute_type']) {
            'pim_catalog_date' => $this->getDateRequiredOperations($normalizedTarget['source_configuration']),
            'pim_catalog_metric' => $this->getMeasurementRequiredOperations($normalizedTarget['source_configuration']),
            'pim_catalog_number' => $this->getNumberRequiredOperations($normalizedTarget['source_configuration']),
            'pim_catalog_price_collection' => $this->getPriceRequiredOperations($normalizedTarget['source_configuration']),
            default => [],
        };
    }

    private function getDateRequiredOperations(array $sourceConfiguration): array
    {
        return [
            new ConvertToDateOperation(
                Uuid::uuid4()->toString(),
                $sourceConfiguration['date_format'],
            ),
        ];
    }

    private function getMeasurementRequiredOperations(array $sourceConfiguration): array
    {
        $uuid = Uuid::uuid4()->toString();

        return [
            new ConvertToMeasurementOperation(
                $uuid,
                $sourceConfiguration['decimal_separator'],
                $sourceConfiguration['unit'],
            ),
        ];
    }

    private function getNumberRequiredOperations(array $sourceConfiguration): array
    {
        $uuid = Uuid::uuid4()->toString();

        return [
            new ConvertToNumberOperation(
                $uuid,
                $sourceConfiguration['decimal_separator'],
            ),
        ];
    }

    private function getPriceRequiredOperations(array $sourceConfiguration): array
    {
        $uuid = Uuid::uuid4()->toString();

        return [
            new ConvertToPriceOperation(
                $uuid,
                $sourceConfiguration['decimal_separator'],
                $sourceConfiguration['currency'],
            ),
        ];
    }
}
