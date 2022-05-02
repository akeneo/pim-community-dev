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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToArrayOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\DecimalFormatterOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;

class OperationCollectionHydrator implements OperationCollectionHydratorInterface
{
    public function hydrate(array $normalizedTarget, array $normalizedOperations): OperationCollection
    {
        return match ($normalizedTarget['type']) {
            AttributeTarget::TYPE => $this->hydrateAttribute($normalizedTarget, $normalizedOperations),
            PropertyTarget::TYPE => $this->hydrateProperty($normalizedTarget['code'], $normalizedOperations),
            default => throw new \InvalidArgumentException(sprintf('Unsupported "%s" target type', $normalizedTarget['type'])),
        };
    }

    public function hydrateAttribute(array $normalizedTarget, array $normalizedOperations): OperationCollection
    {
        $requiredOperations = $this->getRequiredOperations($normalizedTarget);
        $configuredOperations = $this->getConfiguredOperations($normalizedOperations);

        return OperationCollection::create(array_merge($requiredOperations, $configuredOperations));
    }

    public function hydrateProperty(string $propertyCode, array $normalizedOperations): OperationCollection
    {
        // TODO
        return OperationCollection::create([]);
    }

    private function getConfiguredOperations(array $normalizedOperations): array
    {
        return \array_map(
            static fn (array $normalizedOperation) => match ($normalizedOperation['type']) {
                CleanHTMLTagsOperation::TYPE => new CleanHTMLTagsOperation(),
                default => throw new \InvalidArgumentException(sprintf('Unsupported "%s" Operation type', $normalizedOperation['type'])),
            },
            $normalizedOperations,
        );
    }

    private function getRequiredOperations(array $normalizedTarget): array
    {
        return match ($normalizedTarget['type']) {
            'pim_catalog_boolean' => $this->getBooleanRequiredOperations(),
            'pim_catalog_metric' => $this->getMeasurementRequiredOperations($normalizedTarget['source_configuration']),
            'pim_catalog_number' => $this->getNumberRequiredOperations($normalizedTarget['source_configuration']),
            'pim_catalog_multiselect' => $this->getMultiSelectRequiredOperations(),
            default => [],
        };
    }

    private function getMultiSelectRequiredOperations(): array
    {
        return [
            new ConvertToArrayOperation(),
        ];
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
            new DecimalFormatterOperation($sourceConfiguration['decimal_separator']),
        ];
    }

    private function getNumberRequiredOperations(array $sourceConfiguration): array
    {
        return [
            new DecimalFormatterOperation($sourceConfiguration['decimal_separator']),
        ];
    }
}
