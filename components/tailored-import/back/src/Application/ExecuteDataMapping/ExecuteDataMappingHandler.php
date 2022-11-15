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

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentRegistry;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\GetIdentifierAttributeCodeInterface;

class ExecuteDataMappingHandler
{
    public function __construct(
        private OperationApplier $operationApplier,
        private UserIntentRegistry $userIntentRegistry,
        private GetIdentifierAttributeCodeInterface $getIdentifierAttributeCode,
    ) {
    }

    public function handle(ExecuteDataMappingQuery $executeDataMappingQuery): ExecuteDataMappingResult
    {
        $row = $executeDataMappingQuery->getRow();
        $identifierAttributeCode = $this->getIdentifierAttributeCode->execute();
        $userIntents = [];
        $invalidValues = [];
        $productIdentifier = null;

        foreach ($executeDataMappingQuery->getDataMappingCollection() as $dataMapping) {
            $target = $dataMapping->getTarget();
            $sources = $dataMapping->getSources();

            if ($target instanceof AttributeTarget && $target->getCode() === $identifierAttributeCode) {
                $productIdentifier = $row->getCellData($sources[0]);

                continue;
            }

            $value = $this->getFlattenValue($row, $sources);

            if ($value instanceof NullValue) {
                if (TargetInterface::IF_EMPTY_SKIP === $target->getActionIfEmpty()) {
                    continue;
                }
            } else {
                $value = $this->applyOperations($row, $sources, $dataMapping->getOperations());

                // We ignore empty values if they are coming from the operations pipeline
                if ($value instanceof NullValue) {
                    continue;
                }

                if ($value instanceof InvalidValue) {
                    $invalidValues[] = $value;

                    continue;
                }
            }

            $userIntentFactory = $this->userIntentRegistry->getUserIntentFactory($target, $value);
            $userIntents[] = $userIntentFactory->create($target, $value);
        }

        if (null === $productIdentifier) {
            throw new \LogicException('Missing data mapping targeting the identifier attribute');
        }

        return new ExecuteDataMappingResult(
            UpsertProductCommand::createWithIdentifier(
                userId: $executeDataMappingQuery->getUserId(),
                productIdentifier: ProductIdentifier::fromIdentifier($productIdentifier->getValue() ?? ''),
                userIntents: $userIntents,
            ),
            $invalidValues,
        );
    }

    private function getFlattenValue(Row $row, array $sources): ValueInterface
    {
        $processedValues = array_map(static fn (string $source) => $row->getCellData($source), $sources);

        return $this->flattenValues($processedValues);
    }

    private function applyOperations(Row $row, array $sources, OperationCollection $operations): ValueInterface
    {
        $processedValues = [];
        foreach ($sources as $source) {
            $value = $row->getCellData($source);
            $processedValues[] = $this->operationApplier->applyOperations($operations, $value);
        }

        return $this->flattenValues($processedValues);
    }

    private function flattenValues(array $processedValues): ValueInterface
    {
        if (1 === \count($processedValues)) {
            return $processedValues[0];
        }

        $values = array_reduce(
            $processedValues,
            static function (array $reducedValues, ValueInterface $processedValue) {
                if ($processedValue instanceof ArrayValue) {
                    return [...$reducedValues, ...$processedValue->getValue()];
                } else {
                    return [...$reducedValues, $processedValue->getValue()];
                }
            },
            [],
        );

        $uniqueValues = array_filter(array_values(array_unique($values)));

        return empty($uniqueValues) ? new NullValue() : new ArrayValue($uniqueValues);
    }
}
