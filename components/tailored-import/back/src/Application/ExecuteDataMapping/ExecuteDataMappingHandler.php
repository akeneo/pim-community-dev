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

use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentRegistry;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
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

    public function handle(ExecuteDataMappingQuery $executeDataMappingQuery): UpsertProductCommand
    {
        $row = $executeDataMappingQuery->getRow();
        $identifierAttributeCode = $this->getIdentifierAttributeCode->execute();
        $userIntents = [];
        $productIdentifier = null;

        foreach ($executeDataMappingQuery->getDataMappingCollection() as $dataMapping) {
            $target = $dataMapping->getTarget();
            $sources = $dataMapping->getSources();

            if ($target instanceof AttributeTarget && $target->getCode() === $identifierAttributeCode) {
                $productIdentifier = $row->getCellData($sources[0]);

                continue;
            }

            $value = $this->applyOperations($row, $sources, $dataMapping->getOperations());

            if (!$value instanceof NullValue) {
                $userIntentFactory = $this->userIntentRegistry->getUserIntentFactory($target);
                $userIntents[] = $userIntentFactory->create(
                    $target,
                    $value,
                );
            }
        }

        if (null === $productIdentifier) {
            throw new \LogicException('Missing data mapping targeting the identifier attribute');
        }

        return UpsertProductCommand::createFromCollection(
            1,
            $productIdentifier->getValue(),
            $userIntents,
        );
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

        $value = array_reduce(
            $processedValues,
            static function (array $reducedValue, ValueInterface $processedValue) {
                if ($processedValue instanceof ArrayValue)  {
                    return array_merge($reducedValue, $processedValue->getValue());
                } else {
                    return [...$reducedValue, $processedValue->getValue()];
                }
            },
            [],
        );

        return new ArrayValue($value);
    }
}
