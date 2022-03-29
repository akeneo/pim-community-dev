<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceParameterApplier\SourceParameterApplier;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentAggregator\UserIntentAggregatorInterface;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentRegistry;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
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
        private SourceParameterApplier $sourceParameterApplier,
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

            $value = $row->getCellData($sources[0]);

            if ($target instanceof TargetAttribute && $target->getSourceParameter() !== null) {
                $value = $this->sourceParameterApplier->apply($target->getSourceParameter(), $value);
            }

            if ($target instanceof TargetAttribute && $target->getCode() === $identifierAttributeCode) {
                $productIdentifier = $value;
            } else {
                $userIntentFactory = $this->userIntentRegistry->getUserIntentFactory($target);
                $userIntents[] = $userIntentFactory->create($target, $value);
            }
        }

        $userIntents = $this->userIntentAggregator->aggregateByTarget($userIntents);

        return UpsertProductCommand::createFromCollection(
            1,
            $productIdentifier,
            $userIntents,
        );
    }
}
