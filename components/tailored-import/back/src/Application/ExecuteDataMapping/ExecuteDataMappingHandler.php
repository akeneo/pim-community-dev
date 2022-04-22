<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceConfigurationApplier\SourceConfigurationApplier;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentAggregator\UserIntentAggregatorInterface;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentRegistry;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\GetIdentifierAttributeCodeInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExecuteDataMappingHandler
{
    public function __construct(
        private OperationApplier $operationApplier,
        private UserIntentRegistry $userIntentRegistry,
        private UserIntentAggregatorInterface $userIntentAggregator,
        private SourceConfigurationApplier $sourceConfigurationApplier,
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

            $processedValues = [];
            foreach ($sources as $source) {
                $value = $row->getCellData($source);

                if ($target instanceof AttributeTarget && null !== $target->getSourceConfiguration()) {
                    $value = $this->sourceConfigurationApplier->apply($target->getSourceConfiguration(), $value);
                }

                $processedValues[] = $this->operationApplier->applyOperations($dataMapping->getOperations(), $value);
            }

            $userIntentFactory = $this->userIntentRegistry->getUserIntentFactory($target);
            $userIntents[] = $userIntentFactory->create(
                $target,
                1 === \count($sources) ? $processedValues[0] : $processedValues,
            );
        }

        if (null === $productIdentifier) {
            throw new \LogicException('Missing data mapping targeting the identifier attribute');
        }

        $userIntents = $this->userIntentAggregator->aggregateByTarget($userIntents);

        return UpsertProductCommand::createFromCollection(
            1,
            $productIdentifier,
            $userIntents,
        );
    }
}
