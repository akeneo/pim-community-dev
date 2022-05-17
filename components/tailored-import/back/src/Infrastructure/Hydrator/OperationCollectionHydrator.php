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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToDateOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToMeasurementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToNumberOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;

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
                CleanHTMLTagsOperation::TYPE => new CleanHTMLTagsOperation(),
                SplitOperation::TYPE => new SplitOperation($normalizedOperation['separator']),
                default => throw new \InvalidArgumentException(sprintf('Unsupported "%s" Operation type', $normalizedOperation['type'])),
            },
            $normalizedOperations,
        );
    }

    private function getAttributeRequiredOperations(array $normalizedTarget): array
    {
        return match ($normalizedTarget['attribute_type']) {
            'pim_catalog_boolean' => $this->getBooleanRequiredOperations(),
            'pim_catalog_metric' => $this->getMeasurementRequiredOperations($normalizedTarget['source_configuration']),
            'pim_catalog_number' => $this->getNumberRequiredOperations($normalizedTarget['source_configuration']),
            'pim_catalog_date' => $this->getDateRequiredOperations($normalizedTarget['source_configuration']),
            default => [],
        };
    }

    private function getBooleanRequiredOperations(): array
    {
        return [
            new BooleanReplacementOperation([
                '1' => true,
                '0' => false,
            ]),
        ];
    }

    private function getMeasurementRequiredOperations(array $sourceConfiguration): array
    {
        return [
            new ConvertToMeasurementOperation(
                $sourceConfiguration['decimal_separator'],
                $sourceConfiguration['unit'],
            ),
        ];
    }

    private function getNumberRequiredOperations(array $sourceConfiguration): array
    {
        return [
            new ConvertToNumberOperation($sourceConfiguration['decimal_separator']),
        ];
    }

    private function getDateRequiredOperations(array $sourceConfiguration): array
    {
        return [
            new ConvertToDateOperation($sourceConfiguration['date_format']),
        ];
    }
}
